<?php

declare(strict_types=1);

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\Controller;

use Leuchtfeuer\Mautic\Mautic\AuthorizationFactory;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;

class FrontendController extends ActionController
{
    public const DEFAULT_TEMPLATE_PATH = 'EXT:mautic/Resources/Private/Templates/Form.html';

    public function formAction(): ResponseInterface
    {
        if ($this->view instanceof TemplateView) {
            $this->view->setTemplatePathAndFilename($this->getTemplatePath());
        }
        $this->view->assignMultiple([
            // @extensionScannerIgnoreLine
            'mauticBaseUrl' => AuthorizationFactory::createAuthorizationFromExtensionConfiguration()->getBaseUrl(),
            'data' => $this->request->getAttribute('currentContentObject')->data,
        ]);
        return $this->htmlResponse();
    }

    protected function getTemplatePath(): string
    {
        return $this->settings['form']['templatePath'] ?? self::DEFAULT_TEMPLATE_PATH;
    }
}
