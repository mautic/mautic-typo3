<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Transformation\Form;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 *
 ***/

use Bitmotion\Mautic\Domain\Repository\FieldRepository;
use Bitmotion\Mautic\Domain\Repository\FormRepository;
use Bitmotion\Mautic\Transformation\AbstractTransformation;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractFormTransformation extends AbstractTransformation implements SingletonInterface
{
    protected $formType = '';

    protected $formDefinition = [];

    protected $formData = [];

    protected $formElements = [];

    protected $isFormDefinitionUpdated = false;

    protected $shouldUpdateCustomFields = false;

    protected $customFieldValues = [];

    public function __construct(array $formDefinition = [])
    {
        $this->formDefinition = $formDefinition;
    }

    public function transform()
    {
        $this->formData = [
            'alias' => $this->formDefinition['identifier'],
            'formType' => $this->formType,
            'isPublished' => true,
            'name' => $this->formDefinition['label'],
            'postAction' => 'return',
        ];

        $this->enrichFormData();
    }

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function getFormDefinition(): array
    {
        return $this->formDefinition;
    }

    public function setFormDefinition(array $formDefinition)
    {
        $this->formDefinition = $formDefinition;
    }

    public function isShouldUpdateCustomFields(): bool
    {
        return $this->shouldUpdateCustomFields;
    }

    public function removeMauticFormId()
    {
        unset($this->formDefinition['renderingOptions']['mauticId']);
    }

    public function addField(array $fieldDefinition)
    {
        if (!empty($fieldDefinition)) {
            if (!isset($this->formData['fields'])) {
                $this->formData['fields'] = [];
            }

            $this->formData['fields'][] = $fieldDefinition;
        }
    }

    public function getUpdatedFormDefinition(array $response): array
    {
        if ($this->isFormDefinitionUpdated === false) {
            $this->updateFormDefinition($response);
        }

        if (!isset($this->formDefinition['renderingOptions']['mauticId']) && isset($response['form']['id'])) {
            $this->formDefinition['renderingOptions']['mauticId'] = $response['form']['id'];
        }

        if ($this->isShouldUpdateCustomFields()) {
            $this->updateCustomFields();
        }

        return $this->formDefinition;
    }

    protected function enrichFormData()
    {
        if (isset($this->formDefinition['renderingOptions']['mauticId']) && !empty($this->formDefinition['renderingOptions']['mauticId'])) {
            $mauticId = (int)$this->formDefinition['renderingOptions']['mauticId'];

            if ($mauticId !== 0) {
                $formRepository = GeneralUtility::makeInstance(FormRepository::class);
                $mauticForm = $formRepository->getForm($mauticId);

                if (!empty($mauticForm)) {
                    $this->formData = array_replace($mauticForm, $this->formData);
                }
            }
        }
    }

    public function addCustomFieldValues(array $values)
    {
        $this->shouldUpdateCustomFields = true;
        $this->customFieldValues = array_merge($this->customFieldValues, $values);
    }

    protected function updateFormDefinition(array $response)
    {
        // In case Mautic is not reachable, prevent warnings
        if (!\is_array($response['form']['fields'])) {
            return;
        }

        foreach ($response['form']['fields'] as $mauticField) {
            foreach ((array)($this->formDefinition['renderables'] ?? []) as $formPageKey => $formPage) {
                foreach ((array)($formPage['renderables'] ?? []) as $formElementKey => $formElement) {
                    if ($formElement['type'] === 'Fieldset' || $formElement['type'] === 'GridRow') {
                        foreach ((array)$formElement['renderables'] as $containerElementKey => $containerElement) {
                            if ($containerElement['type'] === 'Fieldset' || $containerElement['type'] === 'GridRow') {
                                foreach ((array)$containerElement['renderables'] as $containerElementInnerKey => $containerElementInner) {
                                    if ($mauticField['alias'] === str_replace('-', '_', $containerElementInner['identifier'])) {
                                        $this->formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['renderables'][$containerElementKey]['renderables'][$containerElementInnerKey]['properties']['mauticId'] = $mauticField['id'];
                                        $this->formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['renderables'][$containerElementKey]['renderables'][$containerElementInnerKey]['properties']['mauticAlias'] = str_replace('-', '_', $containerElementInner['identifier']);
                                    }
                                }
                            } else {
                                if ($mauticField['alias'] === str_replace('-', '_', $containerElement['identifier'])) {
                                    $this->formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['renderables'][$containerElementKey]['properties']['mauticId'] = $mauticField['id'];
                                    $this->formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['renderables'][$containerElementKey]['properties']['mauticAlias'] = str_replace('-', '_', $containerElement['identifier']);
                                }
                            }
                        }
                    }
                    if ($mauticField['alias'] === str_replace('-', '_', $formElement['identifier'])) {
                        $this->formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['properties']['mauticId'] = $mauticField['id'];
                        $this->formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['properties']['mauticAlias'] = str_replace('-', '_', $formElement['identifier']);
                    }
                }
            }
        }

        $this->isFormDefinitionUpdated = true;
    }

    /**
     * Returns all form form elements (renderables)
     */
    protected function resolveFormElements(array $formElements, array &$cleanFormElements = []): array
    {
        foreach ($formElements as $formElement) {
            if (isset($formElement['renderables'])) {
                $this->resolveFormElements($formElement['renderables'], $cleanFormElements);
                continue;
            }

            $cleanFormElements[] = $formElement;
        }

        return $cleanFormElements;
    }

    public function getFormElements(): array
    {
        if (empty($this->formElements)) {
            $this->formElements = $this->resolveFormElements($this->formDefinition['renderables']);
        }

        return $this->formElements;
    }

    protected function updateCustomFields()
    {
        $formElements = $this->resolveFormElements($this->formDefinition['renderables']);
        $fieldRepository = GeneralUtility::makeInstance(FieldRepository::class);

        foreach ($this->customFieldValues as $alias => $properties) {
            foreach ($formElements as $formElement) {
                if ($formElement['identifier'] === $alias) {
                    $mauticField = $fieldRepository->getContactFieldByAlias($formElement['properties']['mauticTable']);

                    if (is_array($mauticField) && isset($mauticField['properties'])) {
                        $existingProperties = $mauticField['properties']['list'];
                        $newProperties = [];

                        foreach ($properties as $propertyKey => $property) {
                            foreach ($existingProperties as $existingKey => $existingProperty) {
                                if ($existingProperty['value'] == $property['value']) {
                                    $newProperties[] = [
                                        'label' => $property['label'],
                                        'value' => $property['value'],
                                    ];
                                    unset($existingProperties[$existingKey]);
                                    unset($properties[$propertyKey]);
                                }
                            }
                        }

                        if (!empty($properties)) {
                            $response = $fieldRepository->editContactField(
                                $mauticField['id'],
                                [
                                    'properties' => [
                                        'list' => array_merge($existingProperties, $properties, $newProperties),
                                    ],
                                ]
                            );

                            if (isset($response['errors']) && is_array($response['errors'])) {
                                foreach ($response['errors'] as $error) {
                                    $this->logger->critical($error['code'] . ':' . $error['message']);
                                }
                            }
                        }
                    }
                    break;
                }
            }
        }
    }
}
