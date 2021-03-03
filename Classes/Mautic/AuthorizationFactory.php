<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Mautic;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Florian Wessels <f.wessels@Leuchtfeuer.com>, Leuchtfeuer Digital Marketing
 *
 ***/

use Bitmotion\Mautic\Domain\Model\AccessTokenData;
use Bitmotion\Mautic\Domain\Model\Dto\YamlConfiguration;
use Bitmotion\Mautic\Middleware\AuthorizeMiddleware;
use Mautic\Auth\ApiAuth;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AuthorizationFactory implements SingletonInterface
{
    public const VERSION = 'OAuth1a';
    public const VERSION2 = 'OAuth2';

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
            'version' => $extensionConfiguration->isOauth2() ? self::VERSION2 : self::VERSION,
            'clientKey' => $extensionConfiguration->getPublicKey(),
            'clientSecret' => $extensionConfiguration->getSecretKey(),
            'callback' => self::getCallback($state),
        ];

        if (NULL !== $accessTokenData = AccessTokenData::get()) {
            $translateKeys = [
                'access_token' => 'accessToken',
                'access_token_secret' => 'accessTokenSecret',
                'expires' => 'accessTokenExpires',
                'refresh_token' => 'refreshToken',
            ];
            foreach ($accessTokenData as $key => $value) {
                if (isset($translateKeys[$key])) {
                    $settings[$translateKeys[$key]] = $value;
                }
            }
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
