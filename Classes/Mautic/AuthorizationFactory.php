<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Mautic;

use Mautic\Auth\ApiAuth;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AuthorizationFactory
{
    public static function createAuthorizationFromExtensionConfiguration(array $extensionConfiguration = null): OAuth
    {
        $extensionConfiguration = $extensionConfiguration ?: unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mautic'], ['allowed_classes' => false]);

        $settings = [
            'baseUrl' => $extensionConfiguration['baseUrl'],
            'version' => 'OAuth1a',
            'clientKey' => $extensionConfiguration['publicKey'],
            'clientSecret' => $extensionConfiguration['secretKey'],
            'callback' => $extensionConfiguration['callback'] ?? GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
        ];

        if (!empty($extensionConfiguration['accessToken'])) {
            $settings['accessToken'] = $extensionConfiguration['accessToken'];
            $settings['accessTokenSecret'] = $extensionConfiguration['accessTokenSecret'];
        }

        $initAuth = new ApiAuth();
        $authorization = $initAuth->newAuth($settings);

        return new OAuth($authorization, $settings['baseUrl']);
    }
}
