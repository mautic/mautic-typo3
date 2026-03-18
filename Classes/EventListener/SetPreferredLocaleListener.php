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

use Leuchtfeuer\Mautic\Domain\Repository\ContactRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Frontend\Event\AfterPageAndLanguageIsResolvedEvent;

/**
 * Event listener to set the preferred locale in Mautic based on TYPO3 language.
 * Replaces the deprecated hook: $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess']
 */
final class SetPreferredLocaleListener
{
    private bool $languageNeedsUpdate = false;
    private int $mauticId;

    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly Context $context,
        private readonly SiteFinder $siteFinder
    ) {
        $this->mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
    }

    public function __invoke(AfterPageAndLanguageIsResolvedEvent $event): void
    {
        if (!$this->languageNeedsUpdate || $this->mauticId === 0) {
            return;
        }

        $controller = $event->getController();
        $languageId = $this->context->getPropertyFromAspect('language', 'id');
        // @extensionScannerIgnoreLine
        $site = $this->siteFinder->getSiteByPageId($controller->id);
        $isoCode = $site->getLanguageById($languageId)->getLocale()->getLanguageCode();

        $this->contactRepository->editContact(
            $this->mauticId,
            [
                'preferred_locale' => $isoCode,
            ]
        );
    }

    public function setLanguageNeedsUpdate(bool $needsUpdate): void
    {
        $this->languageNeedsUpdate = $needsUpdate;
    }
}
