<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Middleware;

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
use Bitmotion\Mautic\Domain\Repository\SegmentRepository;
use Bitmotion\Mautic\Domain\Repository\TagRepository;
use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Bitmotion\Mautic\Mautic\OAuth;
use Bitmotion\Mautic\Service\MauticAuthorizeService;
use Mautic\Exception\UnexpectedResponseFormatException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class AuthorizeMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const PATH = '/mautic/authorize';

    protected $state;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if (strpos($path, self::PATH) !== 0) {
            return $handler->handle($request);
        }

        /** @var UserAspect $context */
        $userAspect = GeneralUtility::makeInstance(Context::class)->getAspect('backend.user');
        $this->state = substr($path, strlen(self::PATH) + 1);

        if (empty($this->state) && !$userAspect->isLoggedIn()) {
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

        $hasAccessToken = $authorizeService->hasAccessToken();

        if (!$hasAccessToken) {
            $response = $this->authorize($authorization);
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

    protected function authorize(OAuth $authorization): ?ResponseInterface
    {
        $stream = $this->getStream('Unknown Error', 'An unknown error occurred.');
        $statusCode = 400;

        try {
            if ($authorization->validateAccessToken() && $this->validateState()) {
                if ($authorization->accessTokenUpdated()) {
                    $accessTokenData = $authorization->getAccessTokenData();
                    AccessTokenData::set($accessTokenData);
                }

                /** @var ObjectManager $objectManager */
                $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
                $objectManager->get(SegmentRepository::class)->initializeSegments();
                $objectManager->get(TagRepository::class)->synchronizeTags();

                return null;
            }
        } catch (UnexpectedResponseFormatException $exception) {
            try {
                $errors = \GuzzleHttp\json_decode($exception->getResponse()->getBody(), true)['errors'];
                $error = array_shift($errors);

                $title = sprintf('Error %d', $error['code']);
                $message = $error['message'];
            } catch (InvalidArgumentException $exception) {
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
        $_SESSION['mautic']['oauth']['state'] = $state;
    }

    protected function getState(): string
    {
        if (!session_id()) {
            session_start();
        }

        if (!isset($_SESSION['mautic']['oauth']['state'])) {
            $this->setState();
        }

        return $_SESSION['mautic']['oauth']['state'] ?? '';
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
        } catch (\Exception $e) {
            $random_bytes = openssl_random_pseudo_bytes($length);
        }

        return bin2hex($random_bytes);
    }
}
