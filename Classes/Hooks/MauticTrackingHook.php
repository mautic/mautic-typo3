<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Hooks;

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

use Bitmotion\Mautic\Service\MauticTrackingService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticTrackingHook
{
    /**
     * @var MauticTrackingService
     */
    protected $mauticTrackingService;

    public function __construct(MauticTrackingService $mauticTrackingService = null)
    {
        $this->mauticTrackingService = $mauticTrackingService ?: GeneralUtility::makeInstance(MauticTrackingService::class);
    }

    public function addTrackingCode()
    {
        if ($this->mauticTrackingService->isTrackingEnabled()) {
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->addJsFooterInlineCode('Mautic', $this->mauticTrackingService->getTrackingCode());
        }
    }
}
