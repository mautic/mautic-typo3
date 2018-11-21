<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

use Doctrine\DBAL\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PersonaRepository
{
    public function findBySegments(array $segments): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_marketingautomation_persona');
        $expressionBuilder = $queryBuilder->expr();
        $persona = $queryBuilder->select('*')
            ->from('tx_marketingautomation_persona', 'persona')
            ->leftJoin(
                'persona',
                'tx_marketingautomation_segment_mm',
                'segment',
                $expressionBuilder->eq('persona.uid', $queryBuilder->quoteIdentifier('segment.uid_foreign'))
            )
            ->where(
                $expressionBuilder->in(
                    'segment.uid_local',
                    $queryBuilder->createNamedParameter($segments, Connection::PARAM_INT_ARRAY)
                )
            )
            ->orderBy('persona.sorting')
            ->setMaxResults(1)
            ->execute()
            ->fetchAll();

        return $persona[0] ?? [];
    }
}
