<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Service;

use Bitmotion\Mautic\Controller\BackendController;
use Bitmotion\Mautic\Domain\Model\Dto\EmConfiguration;
use Bitmotion\Mautic\Domain\Repository\SegmentRepository;
use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

class MauticAuthorizeService
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var array
     */
    protected $extensionConfiguration;

    /**
     * @var SegmentRepository
     */
    protected $segmentRepository;

    /**
     * @var string
     */
    protected $minimumMauticVersion = '2.14.2';

    public function __construct(
        array $extensionConfiguration = [],
        AuthInterface $authorization = null,
        SegmentRepository $segmentRepository = null
    ) {
        if (session_id() === '') {
            session_start();
        }

        $this->extensionConfiguration = $extensionConfiguration ?: GeneralUtility::makeInstance(EmConfiguration::class)->getConfigurationArray();
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $this->segmentRepository = $segmentRepository ?: GeneralUtility::makeInstance(SegmentRepository::class, $this->authorization);
    }

    public function validateCredentials(): bool
    {
        if ($this->extensionConfiguration['baseUrl'] === ''
            || $this->extensionConfiguration['publicKey'] === ''
            || $this->extensionConfiguration['secretKey'] === ''
            || $this->extensionConfiguration['authorizeMode'] === ''
        ) {
            $this->showCredentialsInformation();

            return false;
        }

        return true;
    }

    public function getAuthorizeButton(): string
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $title = $this->translate('authorization.withMautic');
        $target = '';
        if ($this->extensionConfiguration['authorizeMode'] !== EmConfiguration::OAUTH1_AUTHORIZATION_MODE) {
            //open authorizaion in new tab to avoid x-frame sameorigin issue in typo3 backend
            $target = 'target="_blank"';
        }

        return '<a href="' . $_SERVER['REQUEST_URI'] . '&amp;tx_marketingauthorizemautic_authorize=1" '
            . 'class="btn btn-default btn-sm" title="' . htmlspecialchars($title) . '" ' . $target . '>'
            . $iconFactory->getIcon('tx_mautic-mautic-icon', Icon::SIZE_SMALL)
            . ' ' . htmlspecialchars($title)
            . '</a>';
    }

    public function checkConnection()
    {
        $api = new MauticApi();
        $api = $api->newApi('contacts', $this->authorization, $this->authorization->getBaseUrl());
        $api->getList('', 0, 1);
        $version = $api->getMauticVersion();

        if ($version === null) {
            $this->addErrorMessage();

            return false;
        }

        if (version_compare($version, $this->minimumMauticVersion, '<')) {
            $this->showIncorrectVersionInformation($version);

            return false;
        }

        unset($_SESSION['oauth']);
        if (empty($_SESSION)) {
            $sessionName = session_name();
            $sessionCookie = session_get_cookie_params();
            setcookie(
                $sessionName,
                '',
                $sessionCookie['lifetime'],
                $sessionCookie['path'],
                $sessionCookie['domain'],
                $sessionCookie['secure']
            );
        }

        if (0 === strpos($this->extensionConfiguration['baseUrl'], 'http:')) {
            $this->showInsecureConnectionInformation();

            return false;
        }

        $this->showSuccessMessage();

        return true;
    }

    protected function showCredentialsInformation()
    {
        $missingInformation = [];
        if ($this->extensionConfiguration['baseUrl'] === '') {
            $missingInformation[] = 'baseUrl';
        }
        if ($this->extensionConfiguration['publicKey'] === '') {
            $missingInformation[] = 'publicKey';
        }
        if ($this->extensionConfiguration['secretKey'] === '') {
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

    public function authorize()
    {
        try {
            if ($this->authorization->validateAccessToken()) {
                if ($this->authorization->accessTokenUpdated()) {
                    $accessTokenData = $this->authorization->getAccessTokenData();
                    $this->extensionConfiguration['accessToken'] = $accessTokenData['access_token'];
                    if ($this->extensionConfiguration['authorizeMode'] === EmConfiguration::OAUTH1_AUTHORIZATION_MODE) {
                        $this->extensionConfiguration['accessTokenSecret'] = $accessTokenData['access_token_secret'];
                    } else {
                        $this->extensionConfiguration['refreshToken'] = $accessTokenData['refresh_token'];
                        $this->extensionConfiguration['expires'] = $accessTokenData['expires'];
                    }
                }

                $this->segmentRepository->initializeSegments();

                GeneralUtility::makeInstance(EmConfiguration::class)->save($this->extensionConfiguration);
                HttpUtility::redirect($_SERVER['REQUEST_URI']);
            }
        } catch (\Exception $exception) {
            $this->addErrorMessage((string)$exception->getCode(), (string)$exception->getMessage());

            return false;
        }

        return true;
    }

    protected function addErrorMessage(string $title = null, string $message = null)
    {
        $title = $title ?: $this->translate('authorization.error.title');
        $message = $message ?: $this->translate('authorization.error.message');

        $this->addFlashMessage(
            GeneralUtility::makeInstance(FlashMessage::class, $message, $title, FlashMessage::ERROR, true)
        );
    }

    protected function addFlashMessage(FlashMessage $message)
    {
        $messageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $messageService->getMessageQueueByIdentifier(BackendController::FLASH_MESSAGE_QUEUE);
        $messageQueue->addMessage($message);
    }

    protected function addWarningMessage(string $title = null, string $message = null)
    {
        $title = $title ?: $this->translate('authorization.warning.title');
        $message = $message ?: $this->translate('authorization.warning.message');

        $this->addFlashMessage(
            GeneralUtility::makeInstance(FlashMessage::class, $message, $title, FlashMessage::WARNING, true)
        );
    }

    protected function showSuccessMessage(string $title = null, string $message = null)
    {
        $title = $title ?: $this->translate('authorization.success.title');
        $message = $message ?: $this->translate('authorization.success.message');

        $this->addFlashMessage(
            GeneralUtility::makeInstance(FlashMessage::class, $message, $title, FlashMessage::OK, true)
        );
    }

    protected function showIncorrectVersionInformation(string $version)
    {
        $title = $this->translate('authorization.wrongMauticVersion.title');
        $message = sprintf(
            $this->translate('authorization.wrongMauticVersion.message'),
            $version,
            $this->minimumMauticVersion
        );

        $this->addErrorMessage($title, $message);
    }

    protected function showInsecureConnectionInformation()
    {
        $title = $this->translate('authorization.insecureConnection.title');
        $message = $this->translate('authorization.insecureConnection.message');

        $this->addWarningMessage($title, $message);
    }

    protected function translate(string $key): string
    {
        return $GLOBALS['LANG']->sL('LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:' . $key);
    }

    public function validateAccessToken(): bool
    {
        if ($this->extensionConfiguration['authorizeMode'] === EmConfiguration::OAUTH1_AUTHORIZATION_MODE) {
            if ($this->extensionConfiguration['accessToken'] !== ''
                && $this->extensionConfiguration['accessTokenSecret'] !== ''
            ) {
                return true;
            }

            return false;
        }

        if ($this->extensionConfiguration['accessToken'] === ''
            || $this->extensionConfiguration['refreshToken'] === ''
        ) {
            return false;
        }

        if ($this->extensionConfiguration['expires'] > 0
            && $this->extensionConfiguration['expires'] > time()
        ) {
            return true;
        }

        return false;
    }

    public function accessTokenToBeRefreshed(): bool
    {
        //Access token have no expire on OAuth 1
        if ($this->extensionConfiguration['authorizeMode'] === EmConfiguration::OAUTH1_AUTHORIZATION_MODE) {
            return false;
        }

        if ($this->extensionConfiguration['accessToken'] === ''
            || $this->extensionConfiguration['refreshToken'] === ''
        ) {
            return false;
        }

        if ($this->extensionConfiguration['expires'] > 0
            && $this->extensionConfiguration['expires'] < time()
        ) {
            return true;
        }

        return false;
    }
}
