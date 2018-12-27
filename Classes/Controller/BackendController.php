<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Controller;

use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class BackendController extends ActionController
{
    /**
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    public function showAction()
    {

    }

    public function authorizeAction()
    {

    }
}