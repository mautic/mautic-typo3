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

use Bitmotion\Mautic\Domain\Model\MauticOauthToken;
use Bitmotion\Mautic\Domain\Repository\MauticOauthTokenRepository;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

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
        $loader = GeneralUtility::makeInstance(YamlFileLoader::class);

        try {
            $conf = $loader->load(GeneralUtility::fixWindowsFilePath($this->fileName), YamlFileLoader::PROCESS_IMPORTS);
        } catch (\Exception $exception) {
            return [];
        }

        if (!$this->isOAuth1()) {
            /** @var MauticOauthTokenRepository $mauticOauthTokenRepository */
            $mauticOauthTokenRepository = GeneralUtility::makeInstance(ObjectManager::class)
                ->get(MauticOauthTokenRepository::class);
            /** @var MauticOauthToken $mauticOauthToken */
            $mauticOauthToken = $mauticOauthTokenRepository->findAll()->getFirst();

            if (!$mauticOauthToken) {
                $this->createMauticOauthToken($conf, $mauticOauthTokenRepository);
                return $conf;
            }

            $conf['accessToken'] = $mauticOauthToken->getAccessToken();
            $conf['refreshToken'] = $mauticOauthToken->getRefreshToken();
            $conf['expires'] = $mauticOauthToken->getExpires();
        }

        return $conf;
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

        if (!$this->isOAuth1()) {
            /** @var MauticOauthTokenRepository $mauticOauthTokenRepository */
            $mauticOauthTokenRepository = GeneralUtility::makeInstance(ObjectManager::class)
                ->get(MauticOauthTokenRepository::class);
            /** @var MauticOauthToken $mauticOauthToken */
            $mauticOauthToken = $mauticOauthTokenRepository->findAll()->getFirst();

            if (!$mauticOauthToken) {
                $this->createMauticOauthToken($configuration, $mauticOauthTokenRepository);
            } else {
                $this->updateMauticOauthToken($configuration, $mauticOauthToken, $mauticOauthTokenRepository);
            }
        }
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

    public function isSameCredentials(array $configuration): bool
    {
        return $this->authorizeMode === $configuration['authorizeMode']
            && $this->secretKey === $configuration['secretKey']
            && $this->publicKey === $configuration['publicKey']
            && $this->baseUrl === $configuration['baseUrl'];
    }

    public function isOAuth1(): bool
    {
        return $this->authorizeMode === YamlConfiguration::OAUTH1_AUTHORIZATION_MODE;
    }

    private function createMauticOauthToken(array $configuration, MauticOauthTokenRepository $mauticOauthTokenRepository): void
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $mauticOauthToken = (new MauticOauthToken())
            ->setAccessToken($configuration['accessToken'])
            ->setRefreshToken($configuration['refreshToken'])
            ->setExpires((int) $configuration['expires']);
        $mauticOauthTokenRepository->add($mauticOauthToken);
        $objectManager->get(PersistenceManager::class)->persistAll();
    }

    private function updateMauticOauthToken(
        array $configuration,
        MauticOauthToken $mauticOauthToken,
        MauticOauthTokenRepository $mauticOauthTokenRepository
    ): void {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $mauticOauthToken->setAccessToken($configuration['accessToken'])
            ->setRefreshToken($configuration['refreshToken'])
            ->setExpires((int) $configuration['expires']);
        $mauticOauthTokenRepository->update($mauticOauthToken);
        $objectManager->get(PersistenceManager::class)->persistAll();
    }
}
