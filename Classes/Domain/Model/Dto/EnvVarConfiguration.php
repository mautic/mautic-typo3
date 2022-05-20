<?php

declare(strict_types=1);

namespace Bitmotion\Mautic\Domain\Model\Dto;

class EnvVarConfiguration extends YamlConfiguration
{
    /**
     * Toujou does only oauth2 by default with mautic
     *
     * @var bool
     */
    protected $oauth2 = true;

    public const ENV_VAR_TO_PROPERTY_MAP = [
        'TOUJOU_MAUTIC_BASE_URL' => 'baseUrl',
        'TOUJOU_MAUTIC_PUBLIC_KEY' => 'publicKey',
        'TOUJOU_MAUTIC_SECRET_KEY' => 'secretKey',
    ];

    /** @noinspection MagicMethodsValidityInspection */
    public function __construct()
    {
        foreach (static::ENV_VAR_TO_PROPERTY_MAP as $envVarName => $propertyName) {
            if ($value = \getenv($envVarName)) {
                $this->{$propertyName} = $value;
            }
        }
    }

    public function save(array $configuration = [])
    {
        throw new \BadFunctionCallException('Saving Environment Variables is no supported.', 1614801391);
    }

    public function getConfigurationArray(): array
    {
        return \array_replace_recursive(
            [
                'oauth2' => $this->oauth2,
                'baseUrl' => $this->baseUrl,
                'publicKey' => $this->publicKey,
                'secretKey' => $this->secretKey,
                'tracking' => $this->tracking,
                'trackingScriptOverride' => $this->trackingScriptOverride,
            ],
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']
        );
    }
}
