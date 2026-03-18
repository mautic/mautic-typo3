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

namespace Leuchtfeuer\Mautic\EventListener;

use Leuchtfeuer\Mautic\Service\MauticTrackingService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;

/**
 * Event listener to add Mautic tracking code to frontend pages.
 * Replaces the deprecated hook: $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']
 */
final class AddMauticTrackingCodeListener
{
    public function __construct(
        private readonly MauticTrackingService $mauticTrackingService,
        private readonly PageRenderer $pageRenderer
    ) {}

    public function __invoke(AfterCacheableContentIsGeneratedEvent $event): void
    {
        if ($this->mauticTrackingService->isTrackingEnabled()) {
            $this->pageRenderer->addJsFooterInlineCode(
                'Mautic',
                $this->mauticTrackingService->getTrackingCode()
            );
        }
    }
}
