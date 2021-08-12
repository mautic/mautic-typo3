<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Model\Dto;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EmConfiguration implements SingletonInterface
{
    public const OAUTH1_AUTHORIZATION_MODE = 'OAuth1a';
    /**
     * @var int
     */
    protected $authorize = 0;

    /**
     * @var string
     */
    protected $baseUrl = '';

    /**
     * @var string
     */
    protected $publicKey = '';

    /**
     * @var string
     */
    protected $secretKey = '';

    /**
     * @var string
     */
    protected $accessToken = '';

    /**
     * @var string
     */
    protected $accessTokenSecret = '';

    /**
     * @var bool
     */
    protected $tracking = false;

    /**
     * @var array
     */
    protected $configurationArray = [];

    /**
     * @var string
     */
    protected $trackingScriptOverride = '';

    /**
     * @var string
     */
    protected $authorizeMode = '';

    /**
     * @var string
     */
    protected $refreshToken = '';

    /**
     * @var int
     */
    protected $expires = 0;

    public function __construct()
    {
        $settings = $this->getRawEmConfig();
        $this->configurationArray = $settings;

        foreach ($settings as $key => $value) {
            if (property_exists(__CLASS__, $key)) {
                $this->$key = $value;
            }
        }
    }

    protected function getRawEmConfig(): array
    {
        try {
            return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('mautic');
        } catch (\Exception $e) {
            return [];
        }
    }

    public function save(array $configuration = [])
    {
        if (!empty($configuration)) {
            $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
            $extensionConfiguration->set('mautic', '', $configuration);
        }
    }

    public function getAuthorize(): int
    {
        return (int)$this->authorize;
    }

    public function getBaseUrl(): string
    {
        return (string)$this->baseUrl;
    }

    public function getPublicKey(): string
    {
        return (string)$this->publicKey;
    }

    public function getSecretKey(): string
    {
        return (string)$this->secretKey;
    }

    public function getAccessToken(): string
    {
        return (string)$this->accessToken;
    }

    public function getAccessTokenSecret(): string
    {
        return (string)$this->accessTokenSecret;
    }

    public function isTracking(): bool
    {
        return (bool)$this->tracking;
    }

    public function getTrackingScriptOverride(): string
    {
        return (string)$this->trackingScriptOverride;
    }

    public function getConfigurationArray(): array
    {
        return $this->configurationArray;
    }

    public function getAuthorizeMode(): string
    {
        return $this->authorizeMode;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getExpires(): int
    {
        return (int)$this->expires;
    }
}
