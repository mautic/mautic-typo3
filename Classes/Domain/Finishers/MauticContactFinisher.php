<?php

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
use TYPO3\CMS\Form\Domain\Model\FormDefinition;

class MauticContactFinisher extends AbstractFinisher
{
    private $mauticService;

    /**
     * MauticContactFinisher constructor.
     */
    public function __construct()
    {
        $this->mauticService = new MauticService();
    }

    protected function executeInternal()
    {
        if (!$this->mauticService->checkConfigPresent()) {
            if (GeneralUtility::getApplicationContext()->isDevelopment()) {
                throw new \InvalidArgumentException('Mautic Username, url and/or Password not set.', 1499940156);
            }

            return;
        }

        $contactApi = $this->mauticService->createMauticApi('contacts');

        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition();

        $formValues = $this->finisherContext->getFormValues();

        $mauticArray = [];

        foreach ($formValues as $key => $value) {
            $mauticType = $this->getMauticType($key, $formDefinition);

            if (!empty($mauticType)) {
                $mauticArray[$mauticType] = $value;
            }
        }

        if (count($mauticArray) > 0) {
            $mauticArray['ipAddress'] = $_SERVER['REMOTE_ADDR'];
            $contactApi->create($mauticArray);
        }
    }

    /**
     * @param string         $field
     * @param FormDefinition $formDefinition
     *
     * @return string
     */
    private function getMauticType(string $field, FormDefinition $formDefinition): string
    {
        $properties = $formDefinition->getElementByIdentifier($field)->getProperties();
        if (!empty($properties['mauticTable'])) {
            $mauticType = $properties['mauticTable'];
        } else {
            $mauticType = '';
        }

        return $mauticType;
    }
}
