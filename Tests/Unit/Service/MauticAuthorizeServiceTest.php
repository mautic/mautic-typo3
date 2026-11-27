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

namespace Leuchtfeuer\Mautic\Tests\Unit\Service;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Mautic\OAuth;
use Leuchtfeuer\Mautic\Service\MauticAuthorizeService;
use Mautic\Auth\OAuth as LibraryOAuth;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class MauticAuthorizeServiceTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    #[Test]
    public function refreshAccessTokenLogsAndReturnsFalseWhenMauticIsUnreachable(): void
    {
        $GLOBALS['LANG'] = $this->createMock(LanguageService::class);
        $configuration = $this->createMock(YamlConfiguration::class);
        $configuration->method('getConfigurationArray')->willReturn([
            'baseUrl' => 'https://m.test',
            'publicKey' => 'public',
            'secretKey' => 'secret',
            'accessToken' => 'access',
            'refreshToken' => 'refresh',
            'expires' => 1,
        ]);
        GeneralUtility::setSingletonInstance(YamlConfiguration::class, $configuration);

        $libraryAuth = $this->createMock(LibraryOAuth::class);
        $libraryAuth->method('validateAccessToken')
            ->willThrowException(new ConnectException('Connection timed out', new Request('POST', 'https://m.test/oauth/v2/token')));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');

        $subject = new MauticAuthorizeService(new OAuth($libraryAuth, 'https://m.test'), false);
        $subject->setLogger($logger);

        self::assertFalse($subject->refreshAccessToken());
    }
}
