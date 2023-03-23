<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Hooks;

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

use Bitmotion\Mautic\Domain\Repository\ContactRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticTagHook
{
    public function setTags(array $params, PageRenderer $pageRenderer)
    {
        $page = $GLOBALS['TSFE']->page;

        if ($page['tx_mautic_tags'] > 0) {
            $tags = $this->getTagsToAssign($page);
            if (!empty($tags)) {
                $contactId = (int)($_COOKIE['mtc_id'] ?? 0);

                if ($contactId > 0) {
                    $contactRepository = GeneralUtility::makeInstance(ContactRepository::class);
                    $contactRepository->editContact($contactId, ['tags' => $tags]);
                }
            }
        }
    }

    protected function getTagsToAssign(array $page): array
    {
        $pageUid = $page['_PAGES_OVERLAY_UID'] ?? $page['uid'];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_mautic_page_tag_mm');
        $result = $queryBuilder
            ->select('uid_foreign')
            ->from('tx_mautic_page_tag_mm')
            ->where($queryBuilder->expr()->eq('uid_local', $pageUid))
            ->execute();

        $tags = [];

        while ($tag = $result->fetchColumn()) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_mautic_domain_model_tag');
            $tags[$tag] = $queryBuilder
                ->select('title')
                ->from('tx_mautic_domain_model_tag')
                ->where($queryBuilder->expr()->eq('uid', $tag))
                ->execute()
                ->fetchColumn();
        }

        return $tags;
    }
}
