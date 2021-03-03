<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Model;


use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AccessTokenData
{

    const REGISTRY_NAMESPACE = 'mautic';
    const REGISTRY_KEY = 'access_token_data';

    public static function get(): ?array
    {
        return GeneralUtility::makeInstance(Registry::class)->get('mautic', 'access_token_data');
    }

    public static function set(array $accessTokenData): void
    {
        GeneralUtility::makeInstance(Registry::class)->set('mautic', 'access_token_data', $accessTokenData);
    }

}
