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
    /**
     * @var int
     */
    protected $authorize = 0;

    /**
     * @var bool
     */
    protected $oauth2 = false;

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

    public function getAuthorize(): int
    {
        return (int)$this->authorize;
    }

    public function isOauth2(): bool
    {
        return (bool)$this->oauth2;
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
}
