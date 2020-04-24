<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Controller;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Florian Wessels <f.wessels@Leuchtfeuer.com>, Leuchtfeuer Digital Marketing
 *
 ***/

use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class FrontendController extends ActionController
{
    const DEFAULT_TEMPLATE_PATH = 'EXT:mautic/Resources/Private/Templates/Form.html';

    public function formAction()
    {
        $this->view->setTemplatePathAndFilename($this->getTemplatePath());
        $this->view->assignMultiple([
            'mauticBaseUrl' => AuthorizationFactory::createAuthorizationFromExtensionConfiguration()->getBaseUrl(),
            'data' => $this->configurationManager->getContentObject()->data,
        ]);
    }

    protected function getTemplatePath(): string
    {
        return $this->settings['form']['templatePath'] ?? self::DEFAULT_TEMPLATE_PATH;
    }
}
