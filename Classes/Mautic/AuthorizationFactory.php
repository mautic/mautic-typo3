<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Mautic;

use Bitmotion\Mautic\Domain\Model\Dto\YamlConfiguration;
use Bitmotion\Mautic\Middleware\AuthorizeMiddleware;
use Mautic\Auth\ApiAuth;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AuthorizationFactory implements SingletonInterface
{
    public const VERSION = 'OAuth1a';

    protected static $oAuth;

    public static function createAuthorizationFromExtensionConfiguration(?string $state = null): OAuth
    {
        if (self::$oAuth instanceof OAuth) {
            return self::$oAuth;
        }

        $extensionConfiguration = GeneralUtility::makeInstance(YamlConfiguration::class);
        $baseUrl = $extensionConfiguration->getBaseUrl();

        $settings = [
            'baseUrl' => $baseUrl,
            'version' => self::VERSION,
            'clientKey' => $extensionConfiguration->getPublicKey(),
            'clientSecret' => $extensionConfiguration->getSecretKey(),
            'callback' => self::getCallback($state),
        ];

        if (!empty($extensionConfiguration->getAccessToken()) && !empty($extensionConfiguration->getAccessTokenSecret())) {
            $settings['accessToken'] = $extensionConfiguration->getAccessToken();
            $settings['accessTokenSecret'] = $extensionConfiguration->getAccessTokenSecret();
        }

        self::$oAuth = new OAuth((new ApiAuth())->newAuth($settings), $baseUrl);

        return self::$oAuth;
    }

    protected static function getCallback(?string $state): string
    {
        $callback = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . AuthorizeMiddleware::PATH;

        if ($state !== null) {
            $callback .= '/' . $state;
        }

        return $callback;
    }
}
