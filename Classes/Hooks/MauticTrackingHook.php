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

namespace Leuchtfeuer\Mautic\Hooks;

use Leuchtfeuer\Mautic\Service\MauticTrackingService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticTrackingHook
{
    /**
     * @var MauticTrackingService
     */
    protected object $mauticTrackingService;

    public function __construct(MauticTrackingService $mauticTrackingService = null)
    {
        $this->mauticTrackingService = $mauticTrackingService ?: GeneralUtility::makeInstance(MauticTrackingService::class);
    }

    public function addTrackingCode(): void
    {
        if ($this->mauticTrackingService->isTrackingEnabled()) {
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->addJsFooterInlineCode(
                name: 'Mautic',
                block: $this->mauticTrackingService->getTrackingCode(),
            );
        }
    }
}
