<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Transformation;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractTransformation implements TransformationInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }
}
