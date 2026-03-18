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

namespace Leuchtfeuer\Mautic\Domain\Finishers;

use Leuchtfeuer\Mautic\Domain\Repository\ContactRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class MauticPointsFinisher extends AbstractFinisher
{
    protected int $mauticId;

    protected object $contactRepository;

    public function __construct()
    {
        $this->contactRepository = GeneralUtility::makeInstance(ContactRepository::class);
        $this->mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
    }

    /**
     * Adds or substracts points to a Mautic contact
     */
    #[\Override]
    protected function executeInternal(): ?string
    {
        $pointsModifier = (int)($this->parseOption('mauticPointsModifier') ?? 0);

        if ($this->mauticId === 0 || $pointsModifier === 0) {
            return null;
        }

        $data = [];
        $data['eventName'] = $this->parseOption('mauticEventName') ?? '';

        $this->contactRepository->modifyContactPoints($this->mauticId, $pointsModifier, $data);
        return null;
    }
}
