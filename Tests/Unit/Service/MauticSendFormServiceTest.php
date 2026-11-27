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
use GuzzleHttp\Psr7\Response;
use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Http\MauticHttpClient;
use Leuchtfeuer\Mautic\Service\MauticAuthorizeService;
use Leuchtfeuer\Mautic\Service\MauticSendFormService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class MauticSendFormServiceTest extends UnitTestCase
{
    private const FORM_URL = 'https://mautic.invalid/form/submit?formId=1';

    protected bool $resetSingletonInstances = true;

    private RequestFactory&MockObject $requestFactory;

    private MauticSendFormService $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $configuration = $this->createMock(YamlConfiguration::class);
        $configuration->method('getTimeout')->willReturn(5);
        $configuration->method('getConfigurationArray')->willReturn(['accessToken' => 'token']);
        GeneralUtility::setSingletonInstance(YamlConfiguration::class, $configuration);

        $authorizeService = $this->createMock(MauticAuthorizeService::class);
        $authorizeService->method('accessTokenToBeRefreshed')->willReturn(false);
        GeneralUtility::addInstance(MauticAuthorizeService::class, $authorizeService);

        $this->requestFactory = $this->createMock(RequestFactory::class);
        $client = new MauticHttpClient($this->requestFactory, $configuration);
        $client->setLogger(new NullLogger());

        $this->subject = new MauticSendFormService($client);
        $this->subject->setLogger(new NullLogger());
    }

    #[Test]
    public function formIsPostedViaInjectedClient(): void
    {
        $this->requestFactory->expects(self::once())->method('request')
            ->with(self::FORM_URL, 'POST', self::callback(static fn(array $options): bool => $options['timeout'] === 5
                && $options['headers']['Authorization'] === 'Bearer token'
                && $options['multipart'] === [['name' => 'mauticform[email]', 'contents' => 'jane@example.com']]))
            ->willReturn(new Response(200));

        self::assertSame(200, $this->subject->submitForm(self::FORM_URL, ['email' => 'jane@example.com']));
    }

    #[Test]
    public function unreachableMauticYieldsStatus500(): void
    {
        $this->requestFactory->method('request')
            ->willThrowException(new ConnectException('Connection timed out', new Request('POST', self::FORM_URL)));

        self::assertSame(500, $this->subject->submitForm(self::FORM_URL, ['email' => 'jane@example.com']));
    }
}
