<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Slot;

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

use Bitmotion\MarketingAutomation\Dispatcher\SubscriberInterface;
use Bitmotion\MarketingAutomation\Persona\Persona;
use Bitmotion\Mautic\Domain\Repository\ContactRepository;
use Bitmotion\Mautic\Domain\Repository\PersonaRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MauticSubscriber implements SubscriberInterface, SingletonInterface
{
    protected $mauticId;

    protected $contactRepository;

    protected $personaRepository;

    protected $languageNeedsUpdate = false;

    public function __construct(ContactRepository $contactRepository, PersonaRepository $personaRepository)
    {
        $this->contactRepository = $contactRepository;
        $this->personaRepository = $personaRepository;

        $this->mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
    }

    public function needsUpdate(Persona $currentPersona, Persona $newPersona): bool
    {
        $isValidMauticId = !empty($this->mauticId);
        $isEmptyPersonaId = empty($currentPersona->getId());
        $this->languageNeedsUpdate = $isValidMauticId && $currentPersona->getLanguage() !== $newPersona->getLanguage();

        return $isValidMauticId && ($isEmptyPersonaId || $this->languageNeedsUpdate);
    }

    public function update(Persona $persona): Persona
    {
        $segments = $this->contactRepository->findContactSegments($this->mauticId);
        $segmentIds = array_map(
            function ($segment) {
                return (int)$segment['id'];
            },
            $segments
        );
        $personaId = $this->personaRepository->findBySegments($segmentIds)['uid'] ?? 0;

        return $persona->withId($personaId);
    }

    public function setPreferredLocale($_, TypoScriptFrontendController $typoScriptFrontendController)
    {
        if ($this->languageNeedsUpdate) {
            $languageId = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id');
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId((int)$typoScriptFrontendController->id);
            $isoCode = $site->getLanguageById($languageId)->getTwoLetterIsoCode();

            $this->contactRepository->editContact(
                $this->mauticId,
                [
                    'preferred_locale' => $isoCode,
                ]
            );
        }
    }
}
