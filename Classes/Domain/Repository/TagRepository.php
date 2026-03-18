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

namespace Leuchtfeuer\Mautic\Domain\Repository;

use Doctrine\DBAL\Exception;
use Mautic\Api\Tags;
use Mautic\Exception\ContextNotFoundException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TagRepository extends AbstractRepository
{
    protected Tags $tagsApi;

    /**
     * @throws ContextNotFoundException
     */
    #[\Override]
    protected function injectApis(): void
    {
        /** @var Tags $api */
        $api = $this->getApi('tags');
        $this->tagsApi = $api;
    }

    public function findAll(): array
    {
        return $this->tagsApi->getList('', 0, 999)['tags'] ?: [];
    }

    public function synchronizeTags(): void
    {
        $availableTags = $this->getAvailableTags();
        $this->deleteAllTags();
        $tags = $this->findAll();
        $time = time();

        foreach ($tags as $tag) {
            if (isset($availableTags[$tag['id']])) {
                $this->updateTag($tag, $time);
            } else {
                $this->insertTag($tag, $time);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function findTagByTitle(string $title): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $result = $queryBuilder->select('*')
            ->from('tx_mautic_domain_model_tag')->where($queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($title, Connection::PARAM_STR)))->executeQuery();
        return $result->fetchAssociative();
    }

    protected function updateTag(array $tag, int $time): void
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->update('tx_mautic_domain_model_tag')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($tag['id'], Connection::PARAM_INT)))
            ->set('tstamp', $time)
            ->set('title', $tag['tag'])->set('deleted', 0)->executeStatement();
    }

    protected function insertTag(array $tag, int $time): void
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->insert('tx_mautic_domain_model_tag')->values([
                'uid' => (int)$tag['id'],
                'crdate' => $time,
                'tstamp' => $time,
                'title' => $tag['tag'],
                'deleted' => 0,
            ])->executeStatement();
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_mautic_domain_model_tag');
    }

    protected function deleteAllTags(): void
    {
        $this->getQueryBuilder()
            ->update('tx_mautic_domain_model_tag')->set('deleted', 1)->executeStatement();
    }

    protected function getAvailableTags(): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();

        $result = $queryBuilder
            ->select('*')->from('tx_mautic_domain_model_tag')->executeQuery();

        $availableTags = [];

        while ($row = $result->fetchAssociative()) {
            $availableTags[$row['uid']] = $row;
        }

        return $availableTags;
    }
}
