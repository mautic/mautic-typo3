<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Slot;

use Bitmotion\MarketingAutomation\Dispatcher\SubscriberInterface;
use Bitmotion\MarketingAutomation\Persona\Persona;
use Bitmotion\Mautic\Domain\Repository\ContactRepository;
use Bitmotion\Mautic\Domain\Repository\PersonaRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MauticSubscriber implements SubscriberInterface, SingletonInterface
{
    /**
     * @var int
     */
    protected $mauticId;

    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    /**
     * @var PersonaRepository
     */
    protected $personaRepository;

    /**
     * @var bool
     */
    protected $languageNeedsUpdate = false;

    public function __construct(
        ContactRepository $contactRepository = null,
        PersonaRepository $personaRepository = null
    ) {
        $this->contactRepository = $contactRepository ?: GeneralUtility::makeInstance(ContactRepository::class);
        $this->personaRepository = $personaRepository ?: GeneralUtility::makeInstance(PersonaRepository::class);

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
