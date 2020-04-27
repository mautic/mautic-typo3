<?php

namespace Bitmotion\Mautic\EventListener;

use Bitmotion\Mautic\Domain\Repository\SegmentRepository;
use Bitmotion\Mautic\Domain\Repository\TagRepository;
use TYPO3\CMS\Backend\Controller\Event\AfterFormEnginePageInitializedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SynchronizeMauticData
{
    public function __invoke(AfterFormEnginePageInitializedEvent $event)
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
