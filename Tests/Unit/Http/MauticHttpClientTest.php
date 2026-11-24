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

namespace Leuchtfeuer\Mautic\Tests\Unit\Http;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Http\MauticHttpClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class MauticHttpClientTest extends UnitTestCase
{
    private const URI = 'https://mautic.test/api/contacts';

    private RequestFactory&MockObject $requestFactory;

    private YamlConfiguration&MockObject $configuration;

    private MauticHttpClient $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestFactory = $this->createMock(RequestFactory::class);
        $this->configuration = $this->createMock(YamlConfiguration::class);
        $this->subject = new MauticHttpClient($this->requestFactory, $this->configuration);
        $this->subject->setLogger(new NullLogger());
    }

    #[Test]
    public function requestPassesConfiguredTimeoutAsTimeoutAndConnectTimeout(): void
    {
        $this->configuration->method('getTimeout')->willReturn(5);
        $this->requestFactory->expects(self::once())->method('request')
            ->with(self::URI, 'GET', ['timeout' => 5, 'connect_timeout' => 5])
            ->willReturn(new Response(200));

        $this->subject->request(self::URI);
    }

    #[Test]
    public function requestWithTimeoutZeroSetsNoTimeoutOptions(): void
    {
        $this->configuration->method('getTimeout')->willReturn(0);
        $this->requestFactory->expects(self::once())->method('request')
            ->with(self::URI, 'GET', [])
            ->willReturn(new Response(200));

        $this->subject->request(self::URI);
    }

    #[Test]
    public function callerOptionsAreKeptAndTimeoutIsAdded(): void
    {
        $this->configuration->method('getTimeout')->willReturn(5);
        $multipart = [['name' => 'mauticform[formId]', 'contents' => '1']];
        $this->requestFactory->expects(self::once())->method('request')
            ->with(self::URI, 'POST', ['multipart' => $multipart, 'timeout' => 5, 'connect_timeout' => 5])
            ->willReturn(new Response(200));

        $this->subject->request(self::URI, 'POST', ['multipart' => $multipart]);
    }

    #[Test]
    public function timeoutIsReadOnEveryRequest(): void
    {
        $this->configuration->method('getTimeout')->willReturnOnConsecutiveCalls(5, 9);
        $passedOptions = [];
        $this->requestFactory->method('request')
            ->willReturnCallback(static function (string $uri, string $method, array $options) use (&$passedOptions): Response {
                $passedOptions[] = $options;
                return new Response(200);
            });

        $this->subject->request(self::URI);
        $this->subject->request(self::URI);

        self::assertSame(
            [['timeout' => 5, 'connect_timeout' => 5], ['timeout' => 9, 'connect_timeout' => 9]],
            $passedOptions
        );
    }

    #[Test]
    public function sendRequestDisablesHttpErrorsAndRedirectsAndForwardsHeadersAndBody(): void
    {
        $this->configuration->method('getTimeout')->willReturn(5);
        $request = new Request('POST', self::URI, ['Authorization' => 'Bearer abc'], 'x');
        $response = new Response(401);
        $this->requestFactory->expects(self::once())->method('request')
            ->with(self::URI, 'POST', [
                'headers' => $request->getHeaders(),
                'body' => $request->getBody(),
                'http_errors' => false,
                'allow_redirects' => false,
                'timeout' => 5,
                'connect_timeout' => 5,
            ])
            ->willReturn($response);

        self::assertSame($response, $this->subject->sendRequest($request));
    }

    #[Test]
    public function sendRequestForwardsBodyAsStreamWithoutBuffering(): void
    {
        $this->configuration->method('getTimeout')->willReturn(5);
        $request = new Request('POST', self::URI, [], 'file content');
        $this->requestFactory->expects(self::once())->method('request')
            ->with(self::URI, 'POST', self::callback(
                static fn(array $options): bool => $options['body'] === $request->getBody()
            ))
            ->willReturn(new Response(201));

        $this->subject->sendRequest($request);
    }

    #[Test]
    public function connectExceptionIsLoggedOnceAndRethrown(): void
    {
        $this->configuration->method('getTimeout')->willReturn(5);
        $this->requestFactory->method('request')->willThrowException($this->createConnectException());
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');
        $this->subject->setLogger($logger);

        $this->expectException(ConnectException::class);
        $this->subject->request(self::URI);
    }

    #[Test]
    public function secondRequestFailsImmediatelyAfterConnectException(): void
    {
        $this->configuration->method('getTimeout')->willReturn(5);
        $this->requestFactory->expects(self::once())->method('request')
            ->willThrowException($this->createConnectException());

        try {
            $this->subject->request(self::URI);
        } catch (ConnectException) {
        }

        $this->expectException(ConnectException::class);
        $this->subject->request(self::URI);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function httpErrorStatusDataProvider(): array
    {
        return [
            'unauthorized' => [401],
            'not found' => [404],
            'server error' => [500],
        ];
    }

    #[Test]
    #[DataProvider('httpErrorStatusDataProvider')]
    public function httpErrorStatusDoesNotMarkMauticUnreachable(int $status): void
    {
        $this->configuration->method('getTimeout')->willReturn(5);
        $request = new Request('GET', self::URI);
        $this->requestFactory->expects(self::exactly(2))->method('request')
            ->willThrowException(RequestException::create($request, new Response($status)));

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $this->subject->request(self::URI);
                self::fail('RequestException expected');
            } catch (RequestException) {
            }
        }
    }

    private function createConnectException(): ConnectException
    {
        return new ConnectException('Connection timed out', new Request('GET', self::URI));
    }
}
