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

namespace Leuchtfeuer\Mautic\Middleware;

use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Domain\Repository\SegmentRepository;
use Leuchtfeuer\Mautic\Domain\Repository\TagRepository;
use Leuchtfeuer\Mautic\Mautic\AuthorizationFactory;
use Leuchtfeuer\Mautic\Mautic\OAuth;
use Leuchtfeuer\Mautic\Service\MauticAuthorizeService;
use Mautic\Exception\UnexpectedResponseFormatException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function GuzzleHttp\json_decode;

class AuthorizeMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const PATH = '/mautic/authorize';

    protected string $state;

    public function __construct(
        private readonly SegmentRepository $segmentRepository,
        private readonly TagRepository $tagRepository
    ) {}

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if (!str_starts_with($path, self::PATH)) {
            return $handler->handle($request);
        }

        $userAspect = GeneralUtility::makeInstance(Context::class)->getAspect('backend.user');
        $this->state = $request->getQueryParams()['state'] ?? '';

        if (($this->state === '' || $this->state === '0') && !$userAspect->isLoggedIn()) {
            return new Response('php://temp', 403);
        }

        return $this->handleRequest();
    }

    protected function validateState(): bool
    {
        return $this->state === $this->getState();
    }

    protected function handleRequest(): ResponseInterface
    {
        $authorization = AuthorizationFactory::createAuthorizationFromExtensionConfiguration($this->getState());
        $authorizeService = new MauticAuthorizeService($authorization, false);
        $statusCode = 400;

        // Authorize TYPO3 when there are no
        if (!$authorizeService->validateCredentials()) {
            $stream = $this->buildStreamFromMessages($authorizeService);

            return new Response($stream, $statusCode);
        }

        $hasAccessToken = $authorizeService->validateAccessToken();

        if (!$hasAccessToken) {
            $response = $this->authorize($authorization, $authorizeService->accessTokenToBeRefreshed());
            if ($response instanceof ResponseInterface) {
                return $response;
            }
        }

        if ($authorizeService->checkConnection()) {
            $stream = $this->getStream('Okay', 'Your Mautic API is now connected. You can close this window and reload your TYPO3 backend.');
            $statusCode = 200;
        } else {
            $stream = $this->buildStreamFromMessages($authorizeService);
        }

        return new Response($stream, $statusCode);
    }

    protected function authorize(OAuth $authorization, bool $refreshToken = false): ?ResponseInterface
    {
        $stream = $this->getStream('Unknown Error', 'An unknown error occurred.');
        $statusCode = 400;

        try {
            if (($this->validateState() || $refreshToken) && $authorization->validateAccessToken()) {
                if ($authorization->accessTokenUpdated()) {
                    $accessTokenData = $authorization->getAccessTokenData();
                    $this->updateExtensionConfiguration($accessTokenData);
                }

                $this->segmentRepository->initializeSegments();
                $this->tagRepository->synchronizeTags();

                return null;
            }
        } catch (UnexpectedResponseFormatException $exception) {
            try {
                $errors = json_decode($exception->getResponse()->getBody(), true)['errors'];
                $error = array_shift($errors);

                $title = sprintf('Error %d', $error['code']);
                $message = $error['message'];
            } catch (\Throwable) {
                $title = $this->translate('authorization.error.title.invalid_response');
                $message = $this->translate('authorization.error.message.invalid_response');
            }

            unset($_SESSION['oauth']);
            $stream = $this->getStream($title, $message);
            $statusCode = 400;
        } catch (\Exception $exception) {
            $title = sprintf('Error %d', $exception->getCode());
            $message = $exception->getMessage();

            unset($_SESSION['oauth']);
            $stream = $this->getStream($title, $message);
            $statusCode = 400;
        }

        return new Response($stream, $statusCode);
    }

    protected function setState(): void
    {
        if (!session_id()) {
            session_start();
        }

        $state = $this->getNonce();
        $_SESSION['oauth']['state'] = $state;
    }

    protected function getState(): string
    {
        if (!session_id()) {
            session_start();
        }

        if (!isset($_SESSION['oauth']['state'])) {
            $this->setState();
        } else {
            $_SESSION['oauth']['state'] = $this->state;
        }

        return $_SESSION['oauth']['state'] ?? '';
    }

    protected function updateExtensionConfiguration(array $accessTokenData): void
    {
        $yamlConfiguration = GeneralUtility::makeInstance(YamlConfiguration::class);

        $extensionConfiguration = $yamlConfiguration->getConfigurationArray();
        $extensionConfiguration['accessToken'] = $accessTokenData['access_token'];
        if ($extensionConfiguration['authorizeMode'] === YamlConfiguration::OAUTH1_AUTHORIZATION_MODE) {
            $extensionConfiguration['accessTokenSecret'] = $accessTokenData['access_token_secret'];
        } else {
            $extensionConfiguration['refreshToken'] = $accessTokenData['refresh_token'];
            $extensionConfiguration['expires'] = $accessTokenData['expires'];
        }

        $yamlConfiguration->save($extensionConfiguration);
    }

    protected function buildStreamFromMessages(MauticAuthorizeService $authorizeService): StreamInterface
    {
        $messages = $authorizeService->getMessages();
        $message = array_shift($messages);

        return $this->getStream($message['title'], $message['message'], $message['severity']);
    }

    protected function getStream(string $title, string $message, int $severity = 0): StreamInterface
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write(
            sprintf(
                '<div class="severity-%d"><h1>%s</h1><p>%s</p></div>',
                $severity,
                $title,
                $message
            )
        );

        return $stream;
    }

    protected function translate(string $key): string
    {
        return $GLOBALS['LANG']->sL('LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:' . $key);
    }

    private function getNonce(int $length = 16): string
    {
        try {
            $random_bytes = random_bytes($length);
        } catch (\Exception) {
            $random_bytes = openssl_random_pseudo_bytes($length);
        }

        return bin2hex($random_bytes);
    }
}
