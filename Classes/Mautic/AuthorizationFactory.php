<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Mautic;

use Bitmotion\Mautic\Domain\Model\Dto\EmConfiguration;
use Mautic\Auth\ApiAuth;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AuthorizationFactory
{
    public static function createAuthorizationFromExtensionConfiguration(): OAuth
    {
        $extensionConfiguration = GeneralUtility::makeInstance(EmConfiguration::class);

        $settings = [
            'baseUrl' => $extensionConfiguration->getBaseUrl(),
            'version' => 'OAuth1a',
            'clientKey' => $extensionConfiguration->getPublicKey(),
            'clientSecret' => $extensionConfiguration->getSecretKey(),
            'callback' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
        ];

        if (!empty($extensionConfiguration['accessToken'])) {
            $settings['accessToken'] = $extensionConfiguration->getAccessToken();
            $settings['accessTokenSecret'] = $extensionConfiguration->getAccessTokenSecret();
        }

        $initAuth = new ApiAuth();
        $authorization = $initAuth->newAuth($settings);

        return new OAuth($authorization, $settings['baseUrl']);
    }
}