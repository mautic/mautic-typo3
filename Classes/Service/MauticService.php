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

namespace Mautic\Mautic\Service;

use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

class MauticService
{
    /**
     * @return \Mautic\Auth\AuthInterface
     */
    public function mauticAuthorization(): \Mautic\Auth\AuthInterface
    {

        // Get the username and password of the mautic installation from the Typo configuration
        $mauticUsername = $this->getConfigurationData('mauticUsername');
        $mauticPassword = $this->getConfigurationData('mauticPassword');

        // Create the authorization array
        $settings = [
            'userName' => $mauticUsername,
            'password' => $mauticPassword,
        ];

        // Initiate the auth object specifying to use BasicAuth
        $initAuth = new ApiAuth();
        $auth     = $initAuth->newAuth($settings, 'BasicAuth');

        // Return the authorization object
        return $auth;
    }

    /**
     * @param string $apiType
     *
     * @return \Mautic\Api\Api
     */
    public function createMauticApi(string $apiType): \Mautic\Api\Api
    {
        // Get the url of the Mautic installation from the Typo configuration
        $apiUrl = $this->getConfigurationData('mauticUrl');

        // Obtian an auth object so it can be used for api calls
        $auth = $this->mauticAuthorization();

        // Instantiate the api object
        $api = new MauticApi();

        return $api->newApi($apiType, $auth, $apiUrl);
    }

    /**
     * @return bool
     */
    public function checkConfigPresent(): bool
    {
        if (empty($this->getConfigurationData('mauticUsername')) || empty($this->getConfigurationData('mauticPassword')) || empty($this->getConfigurationData('mauticUrl'))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getConfigurationData(string $type): string
    {
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mautic'] ?? '');

        return $extensionConfiguration[$type] ?? '';
    }
}
