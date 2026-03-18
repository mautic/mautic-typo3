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

namespace Leuchtfeuer\Mautic\EventListener;

use Leuchtfeuer\Mautic\Domain\Repository\FormRepository;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;

/**
 * Event listener for rendering preview of Mautic form content elements.
 * Replaces the deprecated hook: $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']
 */
final class MauticFormPreviewListener
{
    public function __construct(
        private readonly FormRepository $formRepository
    ) {}

    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getTable() !== 'tt_content') {
            return;
        }

        $record = $event->getRecord();
        if ($record['CType'] !== 'mautic_form') {
            return;
        }

        $mauticForm = $this->formRepository->getForm((int)$record['mautic_form_id']);

        if (empty($mauticForm)) {
            $event->setPreviewContent($this->getFormNotFoundContent((int)$record['mautic_form_id']));
            return;
        }

        $itemContent = '';
        if ($mauticForm['isPublished'] === false) {
            $itemContent .= $this->getFormNotPublishedContent((int)$mauticForm['id'], $mauticForm['name']);
        }

        $itemContent .= '<div class="panel panel-default">';
        $itemContent .= '<table class="table table-hover table-striped">';
        $itemContent .= '<thead>';
        $itemContent .= '<tr><th colspan="2">Mautic Form</th></tr>';
        $itemContent .= '</thead>';
        $itemContent .= '<tbody>';
        $itemContent .= sprintf('<tr><td>ID</td><td>%s</td></tr>', $mauticForm['id']);
        $itemContent .= sprintf('<tr><td>Title</td><td>%s</td></tr>', htmlspecialchars((string)$mauticForm['name']));
        $itemContent .= sprintf('<tr><td>Type</td><td>%s</td></tr>', htmlspecialchars((string)$mauticForm['formType']));
        $itemContent .= sprintf('<tr><td>Published</td><td>%s</td></tr>', $mauticForm['isPublished'] ? 'yes' : 'no');
        $itemContent .= sprintf('<tr><td>Field Count</td><td>%s</td></tr>', count($mauticForm['fields']));
        $itemContent .= sprintf('<tr><td>Kiosk Mode</td><td>%s</td></tr>', $mauticForm['inKioskMode'] ? 'yes' : 'no');
        $itemContent .= '</tbody>';
        $itemContent .= '</table>';
        $itemContent .= '</div>';

        $event->setPreviewContent($itemContent);
    }

    protected function getFormNotFoundContent(int $formId): string
    {
        $content = sprintf('Mautic form with ID %s not found', $formId);

        return
<<<HTML
<div class="alert alert-danger">
    <div class="media">
        <div class="media-left"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-info fa-stack-1x"></i></span></div>
        <div class="media-body">
            <h4 class="alert-title">{$content}</h4>
            <p class="alert-message">Form does not exist or there is no connection to your mautic instance.</p>
        </div>
    </div>
</div>
HTML;
    }

    protected function getFormNotPublishedContent(int $formId, string $formTitle): string
    {
        $content = sprintf('Mautic form with <strong>ID %s</strong> (%s) is not published.', $formId, htmlspecialchars($formTitle));

        return
<<<HTML
<div class="alert alert-warning">
    <div class="media">
        <div class="media-left"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-info fa-stack-1x"></i></span></div>
        <div class="media-body">
            <h4 class="alert-title">Mautic form is not published</h4>
            <p class="alert-message">{$content}</p>
        </div>
    </div>
</div>
HTML;
    }
}
