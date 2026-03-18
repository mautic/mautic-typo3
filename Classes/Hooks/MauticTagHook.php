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

namespace Leuchtfeuer\Mautic\Hooks;

use Leuchtfeuer\Mautic\Domain\Repository\ContactRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MauticTagHook
{
    public function setTags(array $params, TypoScriptFrontendController $frontendController): void
    {
        // Get page record from TYPO3 request or fallback to TSFE
        $page = $frontendController?->page ?? null;

        // Early return if no page found
        if ($page === null) {
            return;
        }
        if ($page['tx_mautic_tags'] > 0) {
            $contactId = (int)($_COOKIE['mtc_id'] ?? 0);

            if ($contactId > 0) {
                $tags = $this->getTagsToAssign($page);
                if ($tags !== []) {
                    $contactRepository = GeneralUtility::makeInstance(ContactRepository::class);
                    $contactRepository->editContact($contactId, ['tags' => $tags]);
                }
            }
        }
    }

    protected function getTagsToAssign(array $page): array
    {
        $pageUid = $page['_PAGES_OVERLAY_UID'] ?? $page['uid'];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_mautic_domain_model_tag');
        $result = $queryBuilder
            ->select('tag.uid', 'tag.title')
            ->from('tx_mautic_domain_model_tag', 'tag')
            ->join('tag', 'tx_mautic_page_tag_mm', 'mm', $queryBuilder->expr()->eq('mm.uid_foreign', $queryBuilder->quoteIdentifier('tag.uid')))
            ->where($queryBuilder->expr()->eq('mm.uid_local', $pageUid))
            ->executeQuery();

        $tags = [];
        while ($row = $result->fetchAssociative()) {
            $tags[$row['uid']] = $row['title'];
        }

        return $tags;
    }
}
