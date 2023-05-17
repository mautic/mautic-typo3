<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Domain\Finishers;

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

use Bitmotion\Mautic\Domain\Repository\FormRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

class MauticFinisher extends AbstractFinisher
{
    protected $formRepository;

    public function __construct(string $finisherIdentifier = '')
    {
        parent::__construct($finisherIdentifier);

        $this->formRepository = GeneralUtility::makeInstance(FormRepository::class);
    }

    /**
     * Post the form result to a Mautic form
     */
    protected function executeInternal()
    {
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition()->getRenderingOptions();
        $mauticId = (int)$this->parseOption('mauticId') ?: (int)($formDefinition['mauticId'] ?? 0);
        $formValues = $this->transformFormStructure($this->finisherContext->getFormValues());

        $this->formRepository->submitForm((int)$mauticId, $formValues);
    }

    /**
     * Transform the TYPO3 form structure to a Mautic structure
     */
    protected function transformFormStructure(array $formStructure): array
    {
        $mauticStructure = [];
        foreach ($formStructure as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $formElement = $this->finisherContext->getFormRuntime()->getFormDefinition()->getElementByIdentifier($key);

            if ($formElement instanceof GenericFormElement) {
                $properties = $formElement->getProperties();
                if (!empty($properties['mauticAlias'])) {
                    $mauticStructure[$properties['mauticAlias']] = $value;
                }
            }
        }

        return $mauticStructure;
    }
}
