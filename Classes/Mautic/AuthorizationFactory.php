<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Mautic;

use Bitmotion\Mautic\Domain\Model\Dto\YamlConfiguration;
use Mautic\Auth\ApiAuth;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AuthorizationFactory
{
    public static function createAuthorizationFromExtensionConfiguration(): OAuth
    {
        $extensionConfiguration = GeneralUtility::makeInstance(YamlConfiguration::class);

        $settings = [
            'baseUrl' => $extensionConfiguration->getBaseUrl(),
            'version' => $extensionConfiguration->getAuthorizeMode(),
            'clientKey' => $extensionConfiguration->getPublicKey(),
            'clientSecret' => $extensionConfiguration->getSecretKey(),
            'callback' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
        ];

        if ($extensionConfiguration->getAccessToken() !== '') {
            $settings['accessToken'] = $extensionConfiguration->getAccessToken();
            if ($extensionConfiguration->isOAuth1()) {
                $settings['accessTokenSecret'] = $extensionConfiguration->getAccessTokenSecret();
            } else {
                $settings['refreshToken'] = $extensionConfiguration->getRefreshToken();
                $settings['accessTokenExpires'] = $extensionConfiguration->getExpires();
            }
        }

        $initAuth = new ApiAuth();
        $authorization = $initAuth->newAuth($settings);

        return new OAuth($authorization, $settings['baseUrl'], $settings['accessToken'] ?? '', $extensionConfiguration->getAuthorizeMode() ?? '');
    }
}
