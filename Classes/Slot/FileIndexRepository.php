<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Slot;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class FileIndexRepository implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function createRecord(array $record)
    {
        $this->logger->debug('createRecord');
        // TODO: Implement when we support file uploads within TYPO3
    }

    public function deleteRecord(int $uid)
    {
        $this->logger->debug('deleteRecord');
        // TODO: Implement when we support file uploads within TYPO3
    }

    public function updateRecord(array $record)
    {
        $this->logger->debug('updateRecord');
        // TODO: Implement when we support file uploads within TYPO3
    }

    public function markRecordAsMissing(array $record)
    {
        $this->logger->debug('markRecordAsMissing');
        // TODO: Implement when we support file uploads within TYPO3
    }
}
