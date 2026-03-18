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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;

/**
 * Event listener to assign Mautic tags to contacts based on page configuration.
 * Replaces the deprecated hook: $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postTransform']
 */
final class AssignMauticTagsListener
{
    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly ConnectionPool $connectionPool
    ) {}

    public function __invoke(AfterCacheableContentIsGeneratedEvent $event): void
    {
        $controller = $event->getController();
        $page = $controller->page;

        if (($page['tx_mautic_tags'] ?? 0) > 0) {
            $tags = $this->getTagsToAssign($page);
            if ($tags !== []) {
                $contactId = (int)($_COOKIE['mtc_id'] ?? 0);

                if ($contactId > 0) {
                    $this->contactRepository->editContact($contactId, ['tags' => $tags]);
                }
            }
        }
    }

    protected function getTagsToAssign(array $page): array
    {
        $pageUid = $page['_PAGES_OVERLAY_UID'] ?? $page['uid'];

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_mautic_page_tag_mm');
        $result = $queryBuilder
            ->select('uid_foreign')
            ->from('tx_mautic_page_tag_mm')
            ->where($queryBuilder->expr()->eq('uid_local', $pageUid))
            ->executeQuery();

        $tags = [];

        while ($tag = $result->fetchOne()) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_mautic_domain_model_tag');
            $tags[$tag] = $queryBuilder
                ->select('title')
                ->from('tx_mautic_domain_model_tag')
                ->where($queryBuilder->expr()->eq('uid', $tag))
                ->executeQuery()
                ->fetchOne();
        }

        return $tags;
    }
}
