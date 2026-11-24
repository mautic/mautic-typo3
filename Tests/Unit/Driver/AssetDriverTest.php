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

namespace Leuchtfeuer\Mautic\Tests\Unit\Driver;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Domain\Repository\AssetRepository;
use Leuchtfeuer\Mautic\Driver\AssetDriver;
use Leuchtfeuer\Mautic\Http\MauticHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class AssetDriverTest extends UnitTestCase
{
    private const DOWNLOAD_URL = 'https://mautic.invalid/asset/1:flyer';

    protected bool $resetSingletonInstances = true;

    private RequestFactory&MockObject $requestFactory;

    private AssetDriver $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $configuration = $this->createMock(YamlConfiguration::class);
        $configuration->method('getTimeout')->willReturn(5);
        $this->requestFactory = $this->createMock(RequestFactory::class);
        $client = new MauticHttpClient($this->requestFactory, $configuration);
        $client->setLogger(new NullLogger());
        GeneralUtility::addInstance(MauticHttpClient::class, $client);

        $assetRepository = $this->createMock(AssetRepository::class);
        $assetRepository->method('list')->willReturn([
            ['id' => 1, 'title' => 'flyer', 'extension' => 'pdf', 'downloadUrl' => self::DOWNLOAD_URL],
        ]);
        GeneralUtility::setSingletonInstance(AssetRepository::class, $assetRepository);

        $this->subject = new class () extends AssetDriver {
            protected function getTemporaryPathForFile(string $fileIdentifier): string
            {
                return (string)tempnam(sys_get_temp_dir(), 'mautic-asset-test-');
            }
        };
        $this->subject->setLogger(new NullLogger());
    }

    #[Test]
    public function assetIsDownloadedViaClient(): void
    {
        $this->requestFactory->expects(self::once())->method('request')
            ->with(self::DOWNLOAD_URL, 'GET', ['timeout' => 5, 'connect_timeout' => 5])
            ->willReturn(new Response(200, [], 'pdf content'));

        $path = $this->subject->getFileForLocalProcessing('/flyer.pdf');

        self::assertStringEqualsFile($path, 'pdf content');
    }

    #[Test]
    public function failedDownloadIsLoggedAndThrowsRuntimeExceptionWithCause(): void
    {
        $connectException = new ConnectException('Connection timed out', new Request('GET', self::DOWNLOAD_URL));
        $this->requestFactory->method('request')->willThrowException($connectException);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');
        $this->subject->setLogger($logger);

        try {
            $this->subject->getFileForLocalProcessing('/flyer.pdf');
            self::fail('RuntimeException expected');
        } catch (\RuntimeException $exception) {
            self::assertSame(1555571766, $exception->getCode());
            self::assertSame($connectException, $exception->getPrevious());
        }
    }
}
