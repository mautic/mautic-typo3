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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MauticSubscriber implements SubscriberInterface, SingletonInterface
{
    protected $mauticId;

    protected $contactRepository;

    protected $personaRepository;

    protected $languageNeedsUpdate = false;

    /**
     * TODO: Rewrite EXT:marketing_automation/Classes/Dispatcher/Dispatcher.php::45 for using constructor autoloader
     */
    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->contactRepository = $objectManager->get(ContactRepository::class);
        $this->personaRepository = $objectManager->get(PersonaRepository::class);

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
            $this->contactRepository->editContact(
                $this->mauticId,
                [
                    'preferred_locale' => $typoScriptFrontendController->sys_language_isocode,
                ]
            );
        }
    }
}
