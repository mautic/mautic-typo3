<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Mautic\Api\Segments;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SegmentRepository
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Segments
     */
    protected $segmentsApi;

    public function __construct(AuthInterface $authorization = null)
    {
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->segmentsApi = $api->newApi('segments', $this->authorization, $this->authorization->getBaseUrl());
    }

    public function findAll(): array
    {
        $segments = $this->segmentsApi->getList();

        return $segments['lists'] ?? [];
    }

    public function initializeSegments()
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_marketingautomation_segment');
        $query = $connection->getDatabasePlatform()->getTruncateTableSQL('tx_marketingautomation_segment');
        $connection->executeQuery($query);
        $query = $connection->getDatabasePlatform()->getTruncateTableSQL('tx_marketingautomation_segment_mm');
        $connection->executeQuery($query);

        $this->synchronizeSegments();
    }

    public function synchronizeSegments()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_marketingautomation_segment');
        $queryBuilder->getRestrictions()->removeAll();

        $result = $queryBuilder->select('*')
            ->from('tx_marketingautomation_segment')
            ->execute();

        $availableSegments = [];
        while ($row = $result->fetch()) {
            $availableSegments[$row['uid']] = $row;
        }
        $result->closeCursor();

        $queryBuilder->update('tx_marketingautomation_segment')
            ->set('deleted', 1)
            ->execute();

        $segments = $this->findAll();
        foreach ($segments as $segment) {
            $dateAdded = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $segment['dateAdded']);
            if (!empty($segment['dateModified'])) {
                $dateModified = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $segment['dateModified']);
            } else {
                $dateModified = \DateTime::createFromFormat('U', (string)$GLOBALS['EXEC_TIME']);
            }

            if (!isset($availableSegments[$segment['id']])) {
                $insertQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('tx_marketingautomation_segment');
                $insertQueryBuilder->insert('tx_marketingautomation_segment')
                    ->values(
                        [
                            'uid' => (int)$segment['id'],
                            'crdate' => $dateAdded->getTimestamp(),
                            'tstamp' => $dateModified->getTimestamp(),
                            'deleted' => (int)!$segment['isPublished'],
                            'title' => $segment['name'],
                        ]
                    )
                    ->execute();
            } else {
                $updateQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('tx_marketingautomation_segment');
                $updateQueryBuilder->update('tx_marketingautomation_segment')
                    ->where(
                        $updateQueryBuilder->expr()->eq(
                            'uid',
                            $updateQueryBuilder->createNamedParameter($segment['id'], \PDO::PARAM_INT)
                        )
                    )
                    ->set('crdate', $dateAdded->getTimestamp())
                    ->set('tstamp', $dateModified->getTimestamp())
                    ->set('deleted', (int)!$segment['isPublished'])
                    ->set('title', $segment['name'])
                    ->execute();
            }
        }
    }
}
