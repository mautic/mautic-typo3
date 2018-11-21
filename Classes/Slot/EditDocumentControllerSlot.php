<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Slot;

use Bitmotion\Mautic\Domain\Repository\SegmentRepository;
use TYPO3\CMS\Backend\Controller\EditDocumentController;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EditDocumentControllerSlot
{
    /**
     * @var SegmentRepository
     */
    protected $segmentRepository;

    public function __construct(SegmentRepository $segmentRepository)
    {
        $this->segmentRepository = $segmentRepository;
    }

    public function synchronizeSegments(EditDocumentController $editDocumentController)
    {
        if (empty(GeneralUtility::_GP('tx_marketingautomation_segments')['updateSegments'])
            || empty($editDocumentController->editconf['tx_marketingautomation_persona'])
        ) {
            return;
        }

        $this->segmentRepository->synchronizeSegments();
    }
}
