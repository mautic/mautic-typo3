<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\MauticTypo3\Domain\Finishers;

use Mautic\MauticTypo3\Service\MauticService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;

class MauticContactFinisher extends AbstractFinisher
{
    private $mauticService;

    /**
     * MauticContactFinisher constructor
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
