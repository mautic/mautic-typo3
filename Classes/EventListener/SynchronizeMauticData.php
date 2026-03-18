<?php

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\EventListener;

use Leuchtfeuer\Mautic\Domain\Repository\SegmentRepository;
use Leuchtfeuer\Mautic\Domain\Repository\TagRepository;
use TYPO3\CMS\Backend\Controller\Event\AfterFormEnginePageInitializedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SynchronizeMauticData
{
    public function __invoke(AfterFormEnginePageInitializedEvent $event): void
    {
        $body = $event->getRequest()->getParsedBody();

        if (isset($body['tx_mautic_domain_model_tag']) && (bool)$body['tx_mautic_domain_model_tag']['updateTags']) {
            GeneralUtility::makeInstance(TagRepository::class)->synchronizeTags();
        }

        if (isset($body['tx_marketingautomation_segments']) && (bool)$body['tx_marketingautomation_segments']['updateSegments']) {
            GeneralUtility::makeInstance(SegmentRepository::class)->synchronizeSegments();
        }
    }
}
