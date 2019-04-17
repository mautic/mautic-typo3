<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Finishers;

use Bitmotion\Mautic\Domain\Repository\ContactRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class MauticPointsFinisher extends AbstractFinisher
{
    protected $mauticId;

    protected $contactRepository;

    public function __construct(string $finisherIdentifier = '')
    {
        parent::__construct($finisherIdentifier);

        $this->contactRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(ContactRepository::class);
        $this->mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
    }

    /**
     * Adds or substracts points to a Mautic contact
     */
    protected function executeInternal()
    {
        $pointsModifier = (int)($this->parseOption('mauticPointsModifier') ?? 0);

        if (0 === $this->mauticId || 0 === $pointsModifier) {
            return;
        }

        $data = [];
        $data['eventName'] = $this->parseOption('mauticEventName') ?? '';

        $this->contactRepository->modifyContactPoints($this->mauticId, $pointsModifier, $data);
    }
}
