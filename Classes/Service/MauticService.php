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

use Escopecz\MauticFormSubmit\Mautic;
use Exception;
use Mautic\Auth\ApiAuth;
use Mautic\Auth\BasicAuth;
use Mautic\Auth\OAuth;
use Mautic\MauticApi;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticService
{
    private $registry;

    public function __construct()
    {
        $this->registry = GeneralUtility::makeInstance('TYPO3\CMS\Core\Registry');
    }

    /**
     * @return \Mautic\Auth\AuthInterface
     */
    public function mauticAuthorization(): \Mautic\Auth\AuthInterface
    {
        if (!$this->checkSessionActive()){
            session_start();
        }
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $settings = [];

        if ($this->isOAuthEnabled()){
            $settings['baseUrl'] = $this->getConfigurationData('mauticUrl');
            $settings['clientKey'] = $this->getConfigurationData('mauticPublicKey');
            $settings['clientSecret'] = $this->getConfigurationData('mauticSecretKey');
            $settings['accessToken'] = $this->registry->get('tx_mautic', 'accessToken', '');
            $settings['accessTokenSecret'] = $this->registry->get('tx_mautic', 'accessTokenSecret', '');
            $settings['version'] = 'OAuth1a';
            $apiType = 'OAuth';
        } else {
            $settings['userName'] = $this->getConfigurationData('mauticUsername');
            $settings['password'] = $this->getConfigurationData('mauticPassword');
            $apiType = 'BasicAuth';
        }

        // Initiate the auth object specifying to use BasicAuth
        $initAuth = new ApiAuth();

        if ($apiType !== 'BasicAuth') {
            $auth = $initAuth->newAuth($settings);
//            if ($auth->validateAccessToken()) {
//                if ($auth->accessTokenUpdated()) {
//                    $accessTokenData = $auth->getAccessTokenData();
//                    $registry = GeneralUtility::makeInstance('TYPO3\CMS\Core\Registry');
//
//                    $registry->set('tx_mautic', 'accessToken', $accessTokenData['access_token']);
//                    $registry->set('tx_mautic', 'accessTokenSecret', $accessTokenData['access_token_secret']);
//                }
//            }


            return $auth;
        } else {
            return $initAuth->newAuth($settings, $apiType);
        }
    }

    /**
     * @param string $apiType
     *
     * @return \Mautic\Api\Api
     * @throws \Mautic\Exception\ContextNotFoundException
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
     * @param array $formValues The data submitted by your form
     * @param string $mauticUrl URL of the mautic installation
     * @param int $formId Mautic Form ID
     *
     * @return mixed
     */
    public function pushForm($formValues, $mauticUrl, $formId)
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($formValues);
        $mautic = new Mautic($mauticUrl);

        $form = $mautic->getForm($formId);

        return $form->submit($formValues);
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

    /**
     * @param string $tag
     * @param string $contactEmail
     * @throws \Mautic\Exception\ContextNotFoundException
     */
    public function addTagToContact(string $tag, string $contactEmail)
    {
        $contactApi = $this->createMauticApi('contacts');

        $data = [
            'email' => $contactEmail,
            'tags' => $tag,
        ];

        $contactApi->create($data);
    }

    private function isOAuthEnabled(): bool
    {
        if (!empty($this->getConfigurationData('mauticUrl'))
            && !empty($this->getConfigurationData('mauticPublicKey'))
            && !empty($this->getConfigurationData('mauticSecretKey'))
            && !empty($this->registry->get('tx_mautic', 'accessToken', ''))
            && !empty($this->registry->get('tx_mautic', 'accessTokenSecret', ''))) {
            return true;
        }

        return false;
    }

    public function testAuth($customAuth = null)
    {
        try {
            // Get the url of the Mautic installation from the Typo configuration
            $apiUrl = $this->getConfigurationData('mauticUrl');

            // Obtian an auth object so it can be used for api calls
            $auth = $this->mauticAuthorization();

            // Instantiate the api object
            $api = new MauticApi();

            $api = $api->newApi('contacts', $customAuth ?? $auth, $apiUrl);
            $response = $api->getList('', 0, 1);
        }catch (Exception $e){
            return null;
        }

        if (empty($api->getMauticVersion())){
            return null;
        }

        $arr = [];
        // Get the version number from the response header:
        $arr['version'] = $api->getMauticVersion();

        if ($auth instanceof BasicAuth){
            $arr['auth_version'] = 'Basic Auth';
        } elseif ($auth instanceof OAuth){
            $arr['auth_version'] = 'OAuth';
        }

        return $arr;
    }

    protected function checkSessionActive()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}
