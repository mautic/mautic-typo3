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

namespace Leuchtfeuer\Mautic\Http;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Sends every request to Mautic with the configured timeout. After the first
 * network failure Mautic is considered unreachable for the rest of the
 * current PHP request, so a page waits for the timeout at most once.
 */
final class MauticHttpClient implements ClientInterface, SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private bool $mauticUnreachable = false;

    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly YamlConfiguration $configuration,
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->request((string)$request->getUri(), $request->getMethod(), [
            'headers' => $request->getHeaders(),
            'body' => (string)$request->getBody(),
            'http_errors' => false,
            'allow_redirects' => false,
        ]);
    }

    /**
     * @param array<string, mixed> $options Guzzle request options
     * @throws ConnectException
     */
    public function request(string $uri, string $method = 'GET', array $options = []): ResponseInterface
    {
        if ($this->mauticUnreachable) {
            throw new ConnectException('Mautic is unreachable during this request', new Request($method, $uri));
        }

        $timeout = $this->configuration->getTimeout();
        if ($timeout > 0) {
            $options['timeout'] = $timeout;
            $options['connect_timeout'] = $timeout;
        }

        try {
            return $this->requestFactory->request($uri, $method, $options);
        } catch (ConnectException $exception) {
            $this->mauticUnreachable = true;
            $this->logger?->warning('Mautic at {host} is unreachable: {message}', [
                'host' => $exception->getRequest()->getUri()->getHost(),
                'message' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    public function isMauticUnreachable(): bool
    {
        return $this->mauticUnreachable;
    }
}
