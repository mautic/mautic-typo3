<?php

declare(strict_types=1);

/*
 * This extension was developed by Beech.it
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Mautic\Mautic\Domain\Finishers;

use Mautic\Mautic\Service\MauticService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class MauticFinisher extends AbstractFinisher
{
    protected function executeInternal()
    {
        $mauticService  = GeneralUtility::makeInstance(MauticService::class);
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition()->getRenderingOptions();

        $mauticId = (int) $this->parseOption('mauticId');

        if (empty($mauticId)) {
            $mauticId = (int) $formDefinition['mauticId'];
        }

        if (!$mauticService->checkConfigPresent()) {
            if (GeneralUtility::getApplicationContext()->isDevelopment()) {
                throw new \InvalidArgumentException('Mautic Username, url and/or Password not set.', 1499940156);
            }

            return;
        }

        if (!empty($mauticId)) {

            // Get the values that were posted in the form and transform them to a format for Mautic
            $formValues = $this->transformFormStructure($this->finisherContext->getFormValues());

            $mauticService->pushForm($formValues, $mauticService->getConfigurationData('mauticUrl'), $mauticId);
        } else {
            if (GeneralUtility::getApplicationContext()->isDevelopment()) {
                throw new \InvalidArgumentException('Your YAML does not appear to contain a valid Mautic Form ID.', 1499940157);
            }

            return;
        }
    }

    /**
     * @param array $formStructure
     *
     * @return array
     */
    private function transformFormStructure(array $formStructure): array
    {
        // Remove null values from the array
        $formStructure = array_filter($formStructure, function ($var) {
            return !is_null($var);
        });

        // Remove empty data so that the post request looks decent
        foreach (array_keys($formStructure, '', true) as $key) {
            unset($formStructure[$key]);
        }

        $toReturn = [];
        // Recreate the array with the Id's of the Mautic fields as Mautic has an oblivious lock on field identifiers
        foreach ($formStructure as $key => $value) {
            // Substitute the TYPO3identifier with the Mautic Alias
            $properties = $this->finisherContext->getFormRuntime()->getFormDefinition()->getElementByIdentifier($key)->getProperties();
            if (!empty($properties['mauticAlias'])) {
                $toReturn[$properties['mauticAlias']] = $value;
            }
        }

        return $toReturn;
    }
}
