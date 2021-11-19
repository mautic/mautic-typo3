<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class MauticOauthTokenRepository extends Repository implements SingletonInterface
{
    public function persistAll(): void
    {
        $this->persistAll();
    }
}
