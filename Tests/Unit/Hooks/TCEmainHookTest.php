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

namespace Leuchtfeuer\Mautic\Tests\Unit\Hooks;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Domain\Repository\TagRepository;
use Leuchtfeuer\Mautic\Hooks\TCEmainHook;
use Leuchtfeuer\Mautic\Http\MauticHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class TCEmainHookTest extends UnitTestCase
{
    private const TRACKING_URL = 'https://mautic.invalid/mtracking.gif?tags=Newsletter';

    protected bool $resetSingletonInstances = true;

    private RequestFactory&MockObject $requestFactory;

    private TagRepository&MockObject $tagRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $configuration = $this->createMock(YamlConfiguration::class);
        $configuration->method('getBaseUrl')->willReturn('https://mautic.invalid');
        $configuration->method('getTimeout')->willReturn(5);
        GeneralUtility::setSingletonInstance(YamlConfiguration::class, $configuration);
        $this->requestFactory = $this->createMock(RequestFactory::class);
        $client = new MauticHttpClient($this->requestFactory, $configuration);
        $client->setLogger(new NullLogger());
        GeneralUtility::addInstance(MauticHttpClient::class, $client);
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->tagRepository->method('findTagByTitle')->willReturn([]);
        GeneralUtility::setSingletonInstance(TagRepository::class, $this->tagRepository);
    }

    #[Test]
    public function newTagIsCreatedInMauticViaClientWithTimeout(): void
    {
        $this->requestFactory->expects(self::once())->method('request')
            ->with(self::TRACKING_URL, 'GET', ['timeout' => 5, 'connect_timeout' => 5])
            ->willReturn(new Response(200));

        $this->createTag();
    }

    #[Test]
    public function unreachableMauticDoesNotBreakSavingTheTag(): void
    {
        $this->requestFactory->method('request')
            ->willThrowException(new ConnectException('Connection timed out', new Request('GET', self::TRACKING_URL)));
        $this->tagRepository->expects(self::once())->method('synchronizeTags');

        $this->createTag();
    }

    private function createTag(): void
    {
        $dataHandler = $this->createMock(DataHandler::class);
        (new TCEmainHook())->processDatamap_afterDatabaseOperations('new', 'tx_mautic_domain_model_tag', 'NEW1', ['title' => 'Newsletter'], $dataHandler);
    }
}
