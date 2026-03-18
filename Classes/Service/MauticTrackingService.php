<?php

declare(strict_types=1);

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\Service;

use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticTrackingService implements SingletonInterface
{
    /**
     * @var YamlConfiguration
     */
    protected object $extensionConfiguration;

    public function __construct()
    {
        $this->extensionConfiguration = GeneralUtility::makeInstance(YamlConfiguration::class);
    }

    public function isTrackingEnabled(): bool
    {
        // @extensionScannerIgnoreLine
        return $this->extensionConfiguration->isTracking() && $this->extensionConfiguration->getBaseUrl() !== '';
    }

    public function getTrackingCode(): string
    {
        if (!$this->isTrackingEnabled()) {
            return '';
        }

        $overrideScript = trim(strip_tags($this->extensionConfiguration->getTrackingScriptOverride()));

        if (!empty($overrideScript)) {
            return $overrideScript;
        }

        // @extensionScannerIgnoreLine - False positive: getBaseUrl() is custom method, not deprecated TYPO3 core method
        return '(function(w,d,t,u,n,a,m){w[\'MauticTrackingObject\']=n;' . 'w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),m=d.getElementsByTagName(t)[0];' . 'a.async=1;a.src=u;m.parentNode.insertBefore(a,m)})(window,document,\'script\',' . GeneralUtility::quoteJSvalue($this->extensionConfiguration->getBaseUrl() . '/mtc.js') . ',\'mt\');mt(\'send\', \'pageview\');';
    }
}
