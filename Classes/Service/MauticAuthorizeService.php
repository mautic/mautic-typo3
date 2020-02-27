<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Service;

use Bitmotion\Mautic\Controller\BackendController;
use Bitmotion\Mautic\Domain\Model\Dto\YamlConfiguration;
use Bitmotion\Mautic\Domain\Repository\SegmentRepository;
use Bitmotion\Mautic\Domain\Repository\TagRepository;
use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Bitmotion\Mautic\Mautic\OAuth;
use GuzzleHttp\Exception\InvalidArgumentException;
use Mautic\Exception\UnexpectedResponseFormatException;
use Mautic\MauticApi;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class MauticAuthorizeService
{
    /**
     * @var OAuth
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
     * @var TagRepository
     */
    protected $tagRepository;

    /**
     * @var string
     */
    protected $minimumMauticVersion = '2.14.2';

    public function __construct()
    {
        if (session_id() === '') {
            session_start();
        }

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->extensionConfiguration = GeneralUtility::makeInstance(YamlConfiguration::class)->getConfigurationArray();
        $this->authorization = AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $this->segmentRepository = $objectManager->get(SegmentRepository::class);
        $this->tagRepository = $objectManager->get(TagRepository::class);
    }

    public function validateCredentials(): bool
    {
        if ($this->extensionConfiguration['baseUrl'] === ''
            || $this->extensionConfiguration['publicKey'] === ''
            || $this->extensionConfiguration['secretKey'] === ''
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

        return '<a href="' . $_SERVER['REQUEST_URI'] . '&amp;tx_marketingauthorizemautic_authorize=1" '
            . 'class="btn btn-default btn-sm" title="' . htmlspecialchars($title) . '">'
            . $iconFactory->getIcon('tx_mautic-mautic-icon', Icon::SIZE_SMALL)
            . ' ' . htmlspecialchars($title)
            . '</a>';
    }

    public function checkConnection()
    {
        // Perform a dummy request for retrieving HTTP headers and getting Mautic Version
        $contactsApi = (new MauticApi())->newApi('contacts', $this->authorization, $this->authorization->getBaseUrl());
        $contactsApi->getList('', 0, 1);
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
                    $this->extensionConfiguration['accessTokenSecret'] = $accessTokenData['access_token_secret'];
                }

                $this->segmentRepository->initializeSegments();
                $this->tagRepository->synchronizeTags();

                GeneralUtility::makeInstance(YamlConfiguration::class)->save($this->extensionConfiguration);
                HttpUtility::redirect($_SERVER['REQUEST_URI']);
            }
        } catch (UnexpectedResponseFormatException $exception) {
            try {
                $errors = \GuzzleHttp\json_decode($exception->getResponse()->getBody(), true)['errors'];

                foreach ($errors as $error) {
                    $this->addErrorMessage('Error ' . (string)$error['code'], (string)$error['message']);
                }
            } catch (InvalidArgumentException $exception) {
                $this->addErrorMessage(
                    $this->translate('authorization.error.title.invalid_response'),
                    $this->translate('authorization.error.message.invalid_response')
                );
            } finally {
                unset($_SESSION['oauth']);

                return false;
            }
        } catch (\Exception $exception) {
            $this->addErrorMessage((string)$exception->getCode(), (string)$exception->getMessage());

            return false;
        }

        return true;
    }

    protected function addErrorMessage(?string $title = null, ?string $message = null)
    {
        $title = $title ?: $this->translate('authorization.error.title');
        $message = $this->translate('authorization.error.message.' . $message) ?: $message ?: $this->translate('authorization.error.message');

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
}
