<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Controller;


use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class FrontendController extends ActionController
{
    public function formAction()
    {
        $this->view->setTemplatePathAndFilename($this->settings['form']['templatePath']);
        $this->view->assignMultiple([
            'mauticBaseUrl' => AuthorizationFactory::createAuthorizationFromExtensionConfiguration()->getBaseUrl(),
            'data' => $this->configurationManager->getContentObject()->data
        ]);
    }
}