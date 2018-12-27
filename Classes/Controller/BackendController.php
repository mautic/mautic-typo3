<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Controller;

use Bitmotion\Mautic\Domain\Model\Dto\EmConfiguration;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class BackendController extends ActionController
{
    /**
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    public function showAction()
    {
        $this->view->assignMultiple([
            'configuration' => new EmConfiguration(),
        ]);
    }

    public function saveAction(array $configuration)
    {
        DebuggerUtility::var_dump($configuration);
        die;
    }
}