<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Hooks;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticTagHook
{
    public function setTags(array $params, PageRenderer $pageRenderer)
    {
        $page = $this->getPage();

        if ($page['tx_mautic_tags'] > 0) {
            $tags = $this->getTagsToAssign($page['uid']);
            $domain = $this->getMauticDomain();
            if (!empty($tags) && $domain !== '') {
                $params['footerData'][] = sprintf(
                    '<img src="%s/mtracking.gif?tags=%s" style="display: none;" />',
                    $domain,
                    implode(',', $tags)
                    );
            }
        }
    }

    protected function getPage(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');

        return $queryBuilder
            ->select('uid', 'tx_mautic_tags')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('uid', $GLOBALS['TSFE']->id))
            ->execute()
            ->fetch() ?? [];
    }

    protected function getMauticDomain(): string
    {
        $domain = '';

        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mautic']) && is_string($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mautic'])) {
            $config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mautic'], ['allowed_classes' => false]);
            $domain = $config['baseUrl'];
        }

        return $domain;
    }

    protected function getTagsToAssign(int $page): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_mautic_page_tag_mm');
        $result = $queryBuilder
            ->select('uid_foreign')
            ->from('tx_mautic_page_tag_mm')
            ->where($queryBuilder->expr()->eq('uid_local', $page))
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
