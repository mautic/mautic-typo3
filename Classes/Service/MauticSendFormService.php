<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Service;

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

use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Cookie;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticSendFormService implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ClientInterface */
    protected $httpClient;

    /** @var RequestFactoryInterface */
    protected $requestFactory;

    /** @var StreamFactoryInterface  */
    protected $streamFactory;

    public function __construct(ClientInterface $httpClient, RequestFactoryInterface $requestFactory, StreamFactoryInterface $streamFactory)
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    public function submitForm(string $url, array $data): int
    {
        $multipartStreamBuilder = new MultipartStreamBuilder($this->streamFactory);
        $this->addDataToMultipartStreamBuilder($multipartStreamBuilder, 'mauticform', $data);

        if (\array_key_exists('mautic_device_id', $_COOKIE)) {
            $multipartStreamBuilder->addResource('mautic_device_id', $_COOKIE['mautic_device_id']);
        }

        $request = $this->requestFactory->createRequest('POST', $url)
            ->withBody($multipartStreamBuilder->build())
            ->withHeader('Content-Type', 'multipart/form-data; boundary="' . $multipartStreamBuilder->getBoundary() . '"');
        $request = $this->addCommonHeadersToRequest($request);
        $request = $this->addCookiesToRequest($request);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Psr\Http\Client\ClientExceptionInterface $e) {
            $this->logger->critical(sprintf('%s: %s', $e->getCode(), $e->getMessage()));
            return 500;
        }

        $statusCode = $response->getStatusCode();

        return (int)$statusCode;
    }

    private function addCommonHeadersToRequest(RequestInterface $request): RequestInterface
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $request = $request->withAddedHeader('Referer', $_SERVER['HTTP_REFERER']);
        }
        $ip = $this->guessIpFromServerGlobal();
        if ($ip !== '') {
            $request = $request
                ->withAddedHeader('X-Forwarded-For', $ip)
                ->withAddedHeader('Client-Ip', $ip);
        }

        return $request;
    }

    protected function guessIpFromServerGlobal(): string
    {
        $ip = '';
        $ipHolders = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($ipHolders as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    // Multiple IPs are present so use the last IP which should be
                    // the most reliable IP that last connected to the proxy
                    $ips = explode(',', $ip);
                    $ips = array_map('trim', $ips);
                    $ip = end($ips);
                }
                $ip = trim($ip);
                break;
            }
        }

        return $ip;
    }

    protected function addCookiesToRequest(RequestInterface $request): RequestInterface
    {
        $cookies = array_filter([
            $this->createProxyCookieIfExists('mtc_id'),
            $this->createProxyCookieIfExists('mtc_sid'),
            $this->createProxyCookieIfExists('mautic_device_id'),
            $this->createProxyCookieIfExists('mautic_session_id')
        ]);

        if (!empty($cookies)) {
            return $request->withHeader('Cookie', implode('; ', $cookies));
        }

        return $request;
    }

    protected function createProxyCookieIfExists(string $cookieName): ?string
    {
        if (\array_key_exists($cookieName, $_COOKIE)) {
            return (string)Cookie::create(
                $cookieName,
                $_COOKIE[$cookieName],
                0,
                null,
                GeneralUtility::getIndpEnv('HTTP_HOST')
            );
        }
        return null;
    }

    protected function addDataToMultipartStreamBuilder(MultipartStreamBuilder $multipartStreamBuilder, string $path, array $data)
    {
        foreach ($data as $key => $value) {
            $tempPath = $path . '[' . $key . ']';
            if (is_array($value)) {
                $this->addDataToMultipartStreamBuilder($multipartStreamBuilder, $tempPath, $value);
            } else {
                $multipartStreamBuilder->addResource($tempPath, (string)$value);
            }
        }
    }
}
