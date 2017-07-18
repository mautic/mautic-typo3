<?php
declare (strict_types = 1);

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\Typo3\Service;

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
        $settings = array(
            'userName' => $mauticUsername,
            'password' => $mauticPassword,
        );

        // Initiate the auth object specifying to use BasicAuth
        $initAuth = new ApiAuth();
        $auth = $initAuth->newAuth($settings, 'BasicAuth');

        // Return the authorization object
        return $auth;
    }


    /**
     * @param string $apiType
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
     * @return string
     */
    public function getConfigurationData(string $type): string
    {
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mautic'] ?? '');
        return $extensionConfiguration[$type] ?? '';
    }

}