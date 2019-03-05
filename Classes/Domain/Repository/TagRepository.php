<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Mautic\Api\Tags;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TagRepository implements SingletonInterface
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Tags
     */
    protected $tagsApi;

    public function __construct(AuthInterface $authorization = null)
    {
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->tagsApi = $api->newApi('tags', $this->authorization, $this->authorization->getBaseUrl());
    }

    public function findAll(): array
    {
        return $this->tagsApi->getList('', 0, 999)['tags'] ?: [];
    }

    public function synchronizeTags()
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

    protected function updateTag(array $tag, int $time)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->update('tx_mautic_domain_model_tag')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($tag['id'], \PDO::PARAM_INT)))
            ->set('tstamp', $time)
            ->set('title', $tag['tag'])
            ->set('deleted', 0)
            ->execute();
    }

    protected function insertTag(array $tag, int $time)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->insert('tx_mautic_domain_model_tag')
            ->values([
                 'uid' => (int)$tag['id'],
                 'crdate' => $time,
                 'tstamp' => $time,
                 'title' => $tag['tag'],
                 'deleted' => 0,
             ])
             ->execute();
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_mautic_domain_model_tag');
    }

    protected function deleteAllTags()
    {
        $this->getQueryBuilder()
            ->update('tx_mautic_domain_model_tag')
            ->set('deleted', 1)
            ->execute();
    }

    protected function getAvailableTags(): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();

        $result = $queryBuilder
            ->select('*')
            ->from('tx_mautic_domain_model_tag')
            ->execute();

        $availableTags = [];

        while ($row = $result->fetch()) {
            $availableTags[$row['uid']] = $row;
        }

        return $availableTags;
    }
}
