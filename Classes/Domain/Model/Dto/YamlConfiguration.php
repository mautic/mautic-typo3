<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Model\Dto;

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

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class YamlConfiguration implements SingletonInterface
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
    protected $configFileName = 'config.yaml';

    /**
     * @var string
     */
    protected $configPath;

    /**
     * @var string
     */
    protected $fileName;

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
        $this->configPath = Environment::getConfigPath() . '/mautic';
        $this->fileName = $this->configPath . '/' . $this->configFileName;
        $this->configurationArray = $this->getYamlConfiguration();
        $extensionConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'];
        $settings = array_replace_recursive($this->configurationArray, $extensionConfiguration);

        foreach ($settings as $key => $value) {
            if (property_exists(__CLASS__, $key)) {
                $this->$key = $value;
            }
        }
    }

    protected function getYamlConfiguration(): array
    {
        if (!file_exists($this->fileName)) return [];
        $loader = GeneralUtility::makeInstance(YamlFileLoader::class);

        try {
            return $loader->load(GeneralUtility::fixWindowsFilePath($this->fileName), YamlFileLoader::PROCESS_IMPORTS);
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * @deprecated Use getYamlConfiguration() instead.
     */
    protected function getRawEmConfig(): array
    {
        trigger_error('Use getYamlConfiguration() instead.', E_USER_DEPRECATED);

        return $this->getYamlConfiguration();
    }

    public function save(array $configuration = [])
    {
        if (!file_exists($this->fileName)) {
            GeneralUtility::mkdir_deep($this->configPath);
        }

        $yamlFileContents = Yaml::dump($configuration, 99, 2);
        GeneralUtility::writeFile($this->fileName, $yamlFileContents);
    }

    public function reloadConfigurations()
    {
        $this->configurationArray = $this->getYamlConfiguration();
        $extensionConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'];
        $settings = array_replace_recursive($this->configurationArray, $extensionConfiguration);

        foreach ($settings as $key => $value) {
            if (property_exists(__CLASS__, $key)) {
                $this->$key = $value;
            }
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
        return empty($this->authorizeMode) ? self::OAUTH1_AUTHORIZATION_MODE : $this->authorizeMode;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getExpires(): int
    {
        return (int)$this->expires;
    }

    public function isSameCredentials(array $configuration): bool
    {
        return $this->authorizeMode === $configuration['authorizeMode']
            && $this->secretKey === $configuration['secretKey']
            && $this->publicKey === $configuration['publicKey']
            && $this->baseUrl === $configuration['baseUrl'];
    }

    public function isOAuth1(): bool
    {
        return $this->getAuthorizeMode() === YamlConfiguration::OAUTH1_AUTHORIZATION_MODE;
    }
}
