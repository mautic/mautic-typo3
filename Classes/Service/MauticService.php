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
     * Push data to a Mautic form.
     *
     * @param array  $formStructure The data submitted by your form
     * @param string $mauticUrl     URL of the mautic installation
     * @param int    $formId        Mautic Form ID
     * @param string $ip            IP address of the lead
     *
     * @return bool
     */
    public function pushForm($formStructure, $mauticUrl, $formId, $ip = null)
    {
        // Get IP from $_SERVER
        if (!$ip) {
            $ipHolders = [
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR',
            ];
            foreach ($ipHolders as $key) {
                if (!empty($_SERVER[$key])) {
                    $ip = $_SERVER[$key];
                    if (strpos($ip, ',') !== false) {
                        // Multiple IPs are present so use the last IP which should be the most reliable IP that last connected to the proxy
                        $ips = explode(',', $ip);
                        array_walk($ips, create_function('&$val', '$val = trim($val);'));
                        $ip = end($ips);
                    }
                    $ip = trim($ip);
                    break;
                }
            }
        }

        $formStructure['formId'] = $formId;

        // return has to be part of the form data array
        if (!isset($formStructure['return'])) {
            $formStructure['return'] = $_SERVER['HTTP_HOST'];
        }

        // Build and initiate the POST
        $formStructurePost = ['mauticform' => $formStructure];
        $formUrl           = $mauticUrl.'/form/submit?formId='.$formId;
        $ch                = curl_init();
        curl_setopt($ch, CURLOPT_URL, $formUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formStructurePost));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Forwarded-For: $ip"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
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

        if ($type === 'mauticUrl') {
            return rtrim($extensionConfiguration[$type], '/');
        }

        return $extensionConfiguration[$type] ?? '';
    }
}
