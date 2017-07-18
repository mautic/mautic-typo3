<?php
declare (strict_types = 1);

namespace BeechIt\Mautic\Service;

/*
* This source file is proprietary property of Beech.it
* Date: 13-4-17
* All code (c) Beech.it all rights reserved
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*
* @license   http://www.opensource.org/licenses/mit-license.html  MIT License
*/

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