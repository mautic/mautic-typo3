<?php

declare(strict_types=1);

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\Mautic;

use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Middleware\AuthorizeMiddleware;
use Leuchtfeuer\Mautic\Service\MauticAuthorizeService;
use Mautic\Auth\ApiAuth;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AuthorizationFactory implements SingletonInterface
{
    public const VERSION = 'OAuth1a';

    protected static ?OAuth $oAuth = null;

    public static function createAuthorizationFromExtensionConfiguration(?string $state = null): OAuth
    {
        if (self::$oAuth instanceof OAuth) {
            return self::$oAuth;
        }

        /** @var YamlConfiguration $extensionConfiguration */
        $extensionConfiguration = GeneralUtility::makeInstance(YamlConfiguration::class);
        // @extensionScannerIgnoreLine
        $baseUrl = $extensionConfiguration->getBaseUrl();

        $settings = [
            'baseUrl' => $baseUrl,
            'version' => $extensionConfiguration->getAuthorizeMode(),
            'clientKey' => $extensionConfiguration->getPublicKey(),
            'clientSecret' => $extensionConfiguration->getSecretKey(),
            'callback' => self::getCallback($state),
        ];

        if (!empty($extensionConfiguration->getAccessToken())) {
            $settings['accessToken'] = $extensionConfiguration->getAccessToken();
            if ($extensionConfiguration->isOAuth1()
                && !empty($extensionConfiguration->getAccessTokenSecret())) {
                $settings['accessTokenSecret'] = $extensionConfiguration->getAccessTokenSecret();
            } else {
                $settings['refreshToken'] = $extensionConfiguration->getRefreshToken();
                $settings['accessTokenExpires'] = $extensionConfiguration->getExpires();
            }
        }

        self::$oAuth = new OAuth(
            (new ApiAuth())->newAuth($settings),
            $baseUrl,
            $settings['accessToken'] ?? '',
            $extensionConfiguration->getAuthorizeMode()
        );

        /** @var MauticAuthorizeService $authorizeService */
        $authorizeService = GeneralUtility::makeInstance(
            MauticAuthorizeService::class,
            self::$oAuth,
            false
        );

        if ($authorizeService->validateCredentials() === true && !$authorizeService->validateAccessToken() && $authorizeService->accessTokenToBeRefreshed()) {
            $authorizeService->refreshAccessToken();
            $extensionConfiguration->reloadConfigurations();
        }

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
