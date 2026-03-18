<?php

declare(strict_types=1);

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\Hooks;

use Leuchtfeuer\Mautic\Domain\Repository\FormRepository;
use Leuchtfeuer\Mautic\Exception\InvalidTransformationClassException;
use Leuchtfeuer\Mautic\Exception\NoTransformationFoundException;
use Leuchtfeuer\Mautic\Exception\TransformationException;
use Leuchtfeuer\Mautic\Exception\UnknownTransformationClassException;
use Leuchtfeuer\Mautic\Transformation\Form\AbstractFormTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\AbstractFormFieldTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\Prototype\ListTransformationPrototype;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Mvc\Configuration\Exception\ParseErrorException;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManagerInterface;

class MauticFormHook implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const FORM_PROTOTYPE_NAME = 'mautic';

    /**
     * @var FormRepository
     */
    protected object $formRepository;

    /**
     * @var array
     */
    protected array $extConf = [];

    /**
     * @var string
     * @deprecated Use self::FORM_PROTOTYPE_NAME instead
     */
    protected string $formPrototypeName = 'mautic';

    /**
     * @var AbstractFormTransformation
     */
    protected $formTransformation;

    public function __construct(protected FormPersistenceManagerInterface $formPersistenceManager)
    {
        $this->formRepository = GeneralUtility::makeInstance(FormRepository::class);
        $this->extConf = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'];
    }

    /**
     * Updates the form in Mautic. Creates a new form if no Mautic form exists, yet.
     */
    public function beforeFormSave(string $formPersistenceIdentifier, array $formDefinition): array
    {
        // Form is not a Mautic form
        if (!$this->isResponsible($formDefinition)) {
            return $formDefinition;
        }

        try {
            $this->transformForm($formDefinition);
            $this->transformFormElements();

            // Update existing Mautic form or create a new one if no form exists
            if (isset($formDefinition['renderingOptions']['mauticId']) && !empty($formDefinition['renderingOptions']['mauticId'])) {
                $formId = (int)$formDefinition['renderingOptions']['mauticId'];

                if ($this->formRepository->formExists($formId)) {
                    $response = $this->formRepository->editForm($formId, $this->formTransformation->getFormData(), true);
                } else {
                    // Remove given mauticId from form definition if mautic form does not exist (e.g. if the form was removed from mautic).
                    $this->formTransformation->removeMauticFormId();
                    $response = $this->formRepository->createForm($this->formTransformation->getFormData());
                }
            } else {
                $response = $this->formRepository->createForm($this->formTransformation->getFormData());
            }

            // returns converted form definition if no error occurred
            if (!$this->logErrorsFromResponse($response)) {
                return $this->formTransformation->getUpdatedFormDefinition($response);
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getCode() . ': ' . $exception->getMessage());
        }

        return $formDefinition;
    }

    /**
     * Creates the duplicated form in Mautic. Duplicate form is treated as a new form
     */
    public function beforeFormDuplicate(string $formPersistenceIdentifier, array $formDefinition): array
    {
        if (!$this->isResponsible($formDefinition)) {
            return $formDefinition;
        }

        // Remove Mautic ID from form
        if (isset($formDefinition['renderingOptions']['mauticId'])) {
            unset($formDefinition['renderingOptions']['mauticId']);
        }

        try {
            $this->transformForm($formDefinition);
            $this->transformFormElements();

            $response = $this->formRepository->createForm($this->formTransformation->getFormData());

            // returns converted form definition if no error occurred
            if (!$this->logErrorsFromResponse($response)) {
                return $this->formTransformation->getUpdatedFormDefinition($response);
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getCode() . ': ' . $exception->getMessage());
        }

        return $formDefinition;
    }

    /**
     * Deletes the form in Mautic
     */
    public function beforeFormDelete(string $formPersistenceIdentifier): string
    {
        $formDefinition = $this->formPersistenceManager->load($formPersistenceIdentifier);

        if ($this->isResponsible($formDefinition) && isset($formDefinition['renderingOptions']['mauticId'])) {
            $response = $this->formRepository->deleteForm((int)$formDefinition['renderingOptions']['mauticId']);
            $this->logErrorsFromResponse($response);
        }

        return $formPersistenceIdentifier;
    }

    protected function isResponsible(array $formDefinition): bool
    {
        if (!isset($formDefinition['prototypeName']) || $formDefinition['prototypeName'] !== self::FORM_PROTOTYPE_NAME) {
            return false;
        }

        return true;
    }

    /**
     * @throws InvalidTransformationClassException
     * @throws NoTransformationFoundException
     * @throws ParseErrorException
     * @throws UnknownTransformationClassException
     */
    protected function transformForm(array $formDefinition): void
    {
        $this->injectFormTransformation($formDefinition);
        $this->formTransformation->transform();
    }

    /**
     * Returns the responsible form transformation class
     *
     * @throws InvalidTransformationClassException
     * @throws NoTransformationFoundException
     * @throws ParseErrorException
     * @throws UnknownTransformationClassException
     */
    protected function injectFormTransformation(array $formDefinition): void
    {
        if (!isset($formDefinition['renderingOptions'])) {
            throw new ParseErrorException('Form has no rendering options.', 1539064345);
        }
        if (!isset($formDefinition['renderingOptions']['mauticFormType'])) {
            throw new ParseErrorException('Form has no Mautic form type.', 1539064529);
        }

        $formType = $formDefinition['renderingOptions']['mauticFormType'];

        // @extensionScannerIgnoreLine
        if (!isset($this->extConf['transformation']['form'][$formType])) {
            throw new NoTransformationFoundException('No transformation class found.', 1539064606);
        }

        // @extensionScannerIgnoreLine
        $transformationClassName = $this->extConf['transformation']['form'][$formType];

        if (!class_exists($transformationClassName)) {
            throw new UnknownTransformationClassException(
                sprintf(
                    'No form transformation class "%s" found.',
                    $transformationClassName
                ),
                1539022440
            );
        }

        $transformationClass = GeneralUtility::makeInstance($transformationClassName, $formDefinition);
        if (!$transformationClass instanceof AbstractFormTransformation) {
            throw new InvalidTransformationClassException(
                sprintf(
                    '%s has to extend %s',
                    $transformationClass::class,
                    AbstractFormTransformation::class
                ),
                1539064754
            );
        }

        $this->formTransformation = $transformationClass;
    }

    /**
     * Get responsible field transformation classes and transform the given field definitions
     *
     * @throws InvalidTransformationClassException
     * @throws NoTransformationFoundException
     * @throws ParseErrorException
     * @throws TransformationException
     * @throws UnknownTransformationClassException
     */
    protected function transformFormElements(): void
    {
        foreach ($this->formTransformation->getFormElements() as $formElement) {
            $fieldTransformation = $this->getFieldTransformation($formElement);
            $fieldTransformation->transform();
            $this->formTransformation->addField($fieldTransformation->getFieldData());
            if ($fieldTransformation instanceof ListTransformationPrototype && $fieldTransformation->hasCustomFieldValues()) {
                $this->formTransformation->addCustomFieldValues($fieldTransformation->getCustomFieldValues());
            }
        }
    }

    /**
     * Returns the responsible field transformation class
     *
     * @throws InvalidTransformationClassException
     * @throws NoTransformationFoundException
     * @throws ParseErrorException
     * @throws UnknownTransformationClassException
     */
    protected function getFieldTransformation(array $formElement): AbstractFormFieldTransformation
    {
        if (!isset($formElement['type'])) {
            throw new ParseErrorException('Form element has no type definition.', 1539064841);
        }
        // @extensionScannerIgnoreLine
        if (!isset($this->extConf['transformation']['formField'][$formElement['type']])) {
            throw new NoTransformationFoundException(
                sprintf('No transformation class for form type "%s" found.', $formElement['type']),
                1539064875
            );
        }
        // @extensionScannerIgnoreLine
        $transformationClassName = $this->extConf['transformation']['formField'][$formElement['type']];
        if (!class_exists($transformationClassName)) {
            throw new UnknownTransformationClassException(
                sprintf(
                    'No field transformation class "%s" found.',
                    $transformationClassName
                ),
                1539022472
            );
        }

        $transformationClass = GeneralUtility::makeInstance($transformationClassName, $formElement);
        if (!$transformationClass instanceof AbstractFormFieldTransformation) {
            throw new InvalidTransformationClassException(
                sprintf(
                    '%s does not extend %s',
                    $transformationClass::class,
                    AbstractFormFieldTransformation::class
                ),
                1539064897
            );
        }

        return $transformationClass;
    }

    protected function logErrorsFromResponse(array $response): bool
    {
        if (isset($response['errors']) && is_array($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $this->logger->critical($error['code'] . ':' . $error['message']);
            }

            return true;
        }

        return false;
    }
}
