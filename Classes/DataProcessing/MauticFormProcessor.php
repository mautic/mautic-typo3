<?php

declare(strict_types=1);

namespace Mautic\Mautic\DataProcessing;

/*
 * This file is part of the package mautic/mautic.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 *
 * (c) 2018 Josua Vogel <josua.vogel@telekom.de>, T-Systems Multimedia Solutions GmbH
 *
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class MauticFormProcessor implements DataProcessorInterface
{
    /**
     * Process data for the content element "Mautic Form".
     *
     * @param ContentObjectRenderer $cObj                       The data of the content element or page
     * @param array                 $contentObjectConfiguration The configuration of Content Object
     * @param array                 $processorConfiguration     The configuration of this processor
     * @param array                 $processedData              Key/value store of processed data (e.g. to be passed to a Fluid View)
     *
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        $extensionConfiguration         = $this->getExtensionConfiguration();
        $url                            = rtrim($extensionConfiguration['mauticUrl']['value'], '/');
        $processedData['mauticBaseUrl'] = $url;

        return $processedData;
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * @return array The current extension configuration
     */
    protected function getExtensionConfiguration()
    {
        /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
        $configurationUtility = $this->getObjectManager()->get(ConfigurationUtility::class);

        return $configurationUtility->getCurrentConfiguration('mautic');
    }
}
