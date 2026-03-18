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

namespace Leuchtfeuer\Mautic\Service;

use Leuchtfeuer\Mautic\Controller\BackendController;
use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Mautic\AuthorizationFactory;
use Leuchtfeuer\Mautic\Mautic\OAuth;
use Leuchtfeuer\Mautic\Middleware\AuthorizeMiddleware;
use Mautic\MauticApi;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticAuthorizeService
{
    protected OAuth $authorization;

    protected array $extensionConfiguration = [];

    protected string $minimumMauticVersion = '2.14.2';

    protected array $messages = [];

    protected LanguageService $languageService;

    public function __construct(OAuth $authorization = null, protected bool $createFlashMessages = true)
    {
        if (session_id() === '') {
            session_start();
        }

        $this->extensionConfiguration = GeneralUtility::makeInstance(YamlConfiguration::class)->getConfigurationArray();
        $this->authorization = $authorization ?? AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $this->languageService = $GLOBALS['LANG'] ?? GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');
    }

    public function validateCredentials(): bool
    {
        if (empty($this->extensionConfiguration['baseUrl'])
            || empty($this->extensionConfiguration['publicKey'])
            || empty($this->extensionConfiguration['secretKey'])
        ) {
            $this->showCredentialsInformation();

            return false;
        }

        return true;
    }

    public function configurationHasAccessToken(): bool
    {
        return !empty($this->extensionConfiguration['accessToken']) && !empty($this->extensionConfiguration['accessTokenSecret']);
    }

    public function getAuthorizeButton(): string
    {
        $title = htmlspecialchars($this->translate('authorization.withMautic'));
        $icon = GeneralUtility::makeInstance(IconFactory::class)->getIcon('tx_mautic-mautic-icon', Icon::SIZE_SMALL);
        $url = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . AuthorizeMiddleware::PATH;

        return sprintf(
            '<a href="%s" class="btn btn-default btn-sm" title="%s" target="_blank">%s %s</a>',
            rawurldecode($url),
            $title,
            $icon,
            $title
        );
    }

    public function checkConnection(): bool
    {
        // Perform a dummy request for retrieving HTTP headers and getting Mautic Version

        // @extensionScannerIgnoreLine
        $contactsApi = (new MauticApi())->newApi('contacts', $this->authorization, $this->authorization->getBaseUrl());
        $contacts = $contactsApi->getList('', 0, 1);

        if ($this->apiCallHasErrors($contacts)) {
            return false;
        }

        $version = $contactsApi->getMauticVersion();

        if ($version === null) {
            $this->addErrorMessage();

            return false;
        }

        if (version_compare($version, $this->minimumMauticVersion, '<')) {
            $this->showIncorrectVersionInformation($version);

            return false;
        }

        unset($_SESSION['oauth']);
        if ($_SESSION === []) {
            $sessionName = session_name();
            $sessionCookie = session_get_cookie_params();
            setcookie(
                $sessionName,
                '',
                ['expires' => $sessionCookie['lifetime'], 'path' => $sessionCookie['path'], 'domain' => $sessionCookie['domain'], 'secure' => $sessionCookie['secure']]
            );
        }

        if (str_starts_with((string)$this->extensionConfiguration['baseUrl'], 'http:')) {
            $this->showInsecureConnectionInformation();

            return false;
        }

        $this->showSuccessMessage();
        return true;
    }

    public function getMessages(): array
    {
        if ($this->createFlashMessages) {
            return [];
        }

        return $this->messages;
    }

    protected function apiCallHasErrors(array $contacts): bool
    {
        if (isset($contacts['errors'])) {
            $error = array_shift($contacts['errors']);
            $title = 'API could not be reached';

            switch ($error['code']) {
                case 403:
                    $message = 'Maybe your API is not enabled. Please check your Mautic configuration.';
                    break;
                case 404:
                    $message = 'Sometimes it is necessary to clear the Mautic cache.';
                    break;
            }

            $message = sprintf(
                'Your Mautic API returned an unexpected status code (%d). %s',
                $error['code'],
                $message ?? ''
            );

            $this->createMessage($message, $title, ContextualFeedbackSeverity::ERROR, true);

            return true;
        }

        return false;
    }

    protected function showCredentialsInformation(): void
    {
        $missingInformation = [];
        if (empty($this->extensionConfiguration['baseUrl'])) {
            $missingInformation[] = 'baseUrl';
        }
        if (empty($this->extensionConfiguration['publicKey'])) {
            $missingInformation[] = 'publicKey';
        }
        if (empty($this->extensionConfiguration['secretKey'])) {
            $missingInformation[] = 'secretKey';
        }

        $this->addErrorMessage(
            $this->translate('authorization.missingInformation.title'),
            sprintf(
                $this->translate('authorization.missingInformation.message'),
                implode(', ', $missingInformation)
            )
        );
    }

    protected function createMessage(string $message, string $title, ContextualFeedbackSeverity $severity, bool $storeInSession = true): void
    {
        if ($this->createFlashMessages) {
            $this->addFlashMessage(new FlashMessage($message, $title, $severity, $storeInSession));
        } else {
            $this->messages[md5($message . $title . $severity->getCssClass())] = [
                'message' => $message,
                'title' => $title,
                'severity' => $severity,
            ];
        }
    }

    protected function addErrorMessage(?string $title = null, ?string $message = null): void
    {
        $title = $title ?: $this->translate('authorization.error.title');
        $message = $this->translate('authorization.error.message.' . $message) ?: $message ?: $this->translate('authorization.error.message');
        $this->createMessage($message, $title, ContextualFeedbackSeverity::ERROR, true);
    }

    protected function addFlashMessage(FlashMessage $message): void
    {
        $messageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $messageService->getMessageQueueByIdentifier(BackendController::FLASH_MESSAGE_QUEUE);
        // @extensionScannerIgnoreLine
        $messageQueue->addMessage($message);
    }

    protected function addWarningMessage(?string $title = null, ?string $message = null): void
    {
        $title = $title ?: $this->translate('authorization.warning.title');
        $message = $message ?: $this->translate('authorization.warning.message');
        $this->createMessage($message, $title, ContextualFeedbackSeverity::WARNING, true);
    }

    protected function showSuccessMessage(?string $title = null, ?string $message = null): void
    {
        $title = $title ?: $this->translate('authorization.success.title');
        $message = $message ?: $this->translate('authorization.success.message');
        $this->createMessage($message, $title, ContextualFeedbackSeverity::OK, true);
    }

    protected function showIncorrectVersionInformation(string $version): void
    {
        $title = $this->translate('authorization.wrongMauticVersion.title');
        $message = sprintf(
            $this->translate('authorization.wrongMauticVersion.message'),
            $version,
            $this->minimumMauticVersion
        );

        $this->addErrorMessage($title, $message);
    }

    protected function showInsecureConnectionInformation(): void
    {
        $title = $this->translate('authorization.insecureConnection.title');
        $message = $this->translate('authorization.insecureConnection.message');
        $this->addWarningMessage($title, $message);
    }

    protected function translate(string $key): string
    {
        if (!$this->languageService instanceof LanguageService) {
            $this->languageService = GeneralUtility::makeInstance(LanguageServiceFactory::class)->createFromUserPreferences($GLOBALS['BE_USER']);
        }
        return $this->languageService->sL('LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:' . $key);
    }

    public function validateAccessToken(): bool
    {
        if (!isset($this->extensionConfiguration['authorizeMode']) || $this->extensionConfiguration['authorizeMode'] === YamlConfiguration::OAUTH1_AUTHORIZATION_MODE) {
            return $this->extensionConfiguration['accessToken'] !== '' && $this->extensionConfiguration['accessTokenSecret'] !== '';
        }

        if ($this->extensionConfiguration['accessToken'] === '' || $this->extensionConfiguration['refreshToken'] === '') {
            return false;
        }

        return $this->extensionConfiguration['expires'] > time();
    }

    public function accessTokenToBeRefreshed(): bool
    {
        //Access token have no expire on OAuth 1
        if ($this->extensionConfiguration['authorizeMode'] === YamlConfiguration::OAUTH1_AUTHORIZATION_MODE) {
            return false;
        }

        if ($this->extensionConfiguration['accessToken'] === ''
            || $this->extensionConfiguration['refreshToken'] === ''
        ) {
            return false;
        }

        return $this->extensionConfiguration['expires'] < time();
    }

    public function refreshAccessToken(): bool
    {
        try {
            if ($this->authorization->validateAccessToken() && $this->authorization->accessTokenUpdated()) {
                $accessTokenData = $this->authorization->getAccessTokenData();
                $this->extensionConfiguration['accessToken'] = $accessTokenData['access_token'];
                if ($this->extensionConfiguration['authorizeMode'] === YamlConfiguration::OAUTH1_AUTHORIZATION_MODE) {
                    $this->extensionConfiguration['accessTokenSecret'] = $accessTokenData['access_token_secret'];
                } else {
                    $this->extensionConfiguration['refreshToken'] = $accessTokenData['refresh_token'];
                    $this->extensionConfiguration['expires'] = $accessTokenData['expires'];
                }

                GeneralUtility::makeInstance(YamlConfiguration::class)->save($this->extensionConfiguration);
            }
        } catch (\Exception $exception) {
            $this->addErrorMessage((string)$exception->getCode(), $exception->getMessage());

            return false;
        }

        return $this->checkConnection();
    }
}
