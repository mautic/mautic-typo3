<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Service;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 *
 ***/

use Bitmotion\Mautic\Domain\Model\Dto\YamlConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticTrackingService implements SingletonInterface
{
    /**
     * @var YamlConfiguration
     */
    protected $extensionConfiguration;

    public function __construct()
    {
        $this->extensionConfiguration = GeneralUtility::makeInstance(YamlConfiguration::class);
    }

    public function isTrackingEnabled(): bool
    {
        return $this->extensionConfiguration->isTracking() && $this->extensionConfiguration->getBaseUrl() !== '';
    }

    public function getTrackingCode(): string
    {
        if (!$this->isTrackingEnabled()) {
            return '';
        }

        if (!empty($this->extensionConfiguration->getTrackingScriptOverride())) {
            return $this->extensionConfiguration->getTrackingScriptOverride();
        }

        return '(function(w,d,t,u,n,a,m){w[\'MauticTrackingObject\']=n;'
            . 'w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),m=d.getElementsByTagName(t)[0];'
            . 'a.async=1;a.src=u;m.parentNode.insertBefore(a,m)})(window,document,\'script\','
            . GeneralUtility::quoteJSvalue($this->extensionConfiguration->getBaseUrl() . '/mtc.js')
            . ',\'mt\');mt(\'send\', \'pageview\');';
    }
}
