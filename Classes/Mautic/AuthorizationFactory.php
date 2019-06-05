<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Mautic;

use Bitmotion\Mautic\Domain\Model\Dto\YamlConfiguration;
use Mautic\Auth\ApiAuth;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AuthorizationFactory implements SingletonInterface
{
    protected static $oAuth;

    public static function createAuthorizationFromExtensionConfiguration(): OAuth
    {
        if (self::$oAuth instanceof OAuth) {
            return self::$oAuth;
        }

        $extensionConfiguration = GeneralUtility::makeInstance(YamlConfiguration::class);

        $settings = [
            'baseUrl' => $extensionConfiguration->getBaseUrl(),
            'version' => 'OAuth1a',
            'clientKey' => $extensionConfiguration->getPublicKey(),
            'clientSecret' => $extensionConfiguration->getSecretKey(),
            'callback' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
        ];

        if ($extensionConfiguration->getAccessToken() !== '') {
            $settings['accessToken'] = $extensionConfiguration->getAccessToken();
            $settings['accessTokenSecret'] = $extensionConfiguration->getAccessTokenSecret();
        }

        $initAuth = new ApiAuth();
        $authorization = $initAuth->newAuth($settings);

        $oAuth = new OAuth($authorization, $settings['baseUrl']);
        self::$oAuth = $oAuth;

        return $oAuth;
    }
}
