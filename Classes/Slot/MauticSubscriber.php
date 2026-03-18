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

namespace Leuchtfeuer\Mautic\Slot;

use Leuchtfeuer\MarketingAutomation\Dispatcher\SubscriberInterface;
use Leuchtfeuer\MarketingAutomation\Persona\Persona;
use Leuchtfeuer\Mautic\Domain\Repository\ContactRepository;
use Leuchtfeuer\Mautic\Domain\Repository\PersonaRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MauticSubscriber implements SubscriberInterface, SingletonInterface
{
    protected int $mauticId;

    protected bool $languageNeedsUpdate = false;

    public function __construct(protected ContactRepository $contactRepository, protected PersonaRepository $personaRepository)
    {
        $this->mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
    }

    #[\Override]
    public function needsUpdate(Persona $currentPersona, Persona $newPersona): bool
    {
        $isValidMauticId = $this->mauticId !== 0;
        $isEmptyPersonaId = $currentPersona->getId() === 0;
        $this->languageNeedsUpdate = $isValidMauticId && $currentPersona->getLanguage() !== $newPersona->getLanguage();

        return $isValidMauticId && ($isEmptyPersonaId || $this->languageNeedsUpdate);
    }

    #[\Override]
    public function update(Persona $persona): Persona
    {
        $segments = $this->contactRepository->findContactSegments($this->mauticId);
        $segmentIds = array_map(
            fn($segment): int => (int)$segment['id'],
            $segments
        );
        $personaId = $this->personaRepository->findBySegments($segmentIds)['uid'] ?? 0;

        return $persona->withId($personaId);
    }

    public function setPreferredLocale(mixed $_, TypoScriptFrontendController $typoScriptFrontendController): void
    {
        if ($this->languageNeedsUpdate) {
            $languageId = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id');
            // @extensionScannerIgnoreLine
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($typoScriptFrontendController->id);
            $isoCode = $site->getLanguageById($languageId)->getLocale()->getLanguageCode();

            $this->contactRepository->editContact(
                $this->mauticId,
                [
                    'preferred_locale' => $isoCode,
                ]
            );
        }
    }
}
