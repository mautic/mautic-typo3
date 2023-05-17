<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Hooks\PageLayoutView;

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

use Bitmotion\Mautic\Domain\Repository\FormRepository;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticFormPreviewRenderer implements PageLayoutViewDrawItemHookInterface
{
    /**
     * Preprocesses the preview rendering of a content element of type "Dynamic Content".
     *
     * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject Calling parent object
     * @param bool $drawItem Whether to draw the item using the default functionality
     * @param string $headerContent Header content
     * @param string $itemContent Item content
     * @param array $row Record row of tt_content
     */
    public function preProcess(PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row)
    {
        if ($row['CType'] === 'mautic_form') {
            $formRepository = GeneralUtility::makeInstance(FormRepository::class);
            $mauticForm = $formRepository->getForm((int)$row['mautic_form_id']);

            if (empty($mauticForm)) {
                $itemContent .= $this->getFormNotFoundContent((int)$row['mautic_form_id']);
            } else {
                if ($mauticForm['isPublished'] === false) {
                    $itemContent .= $this->getFormNotPublishedContent((int)$mauticForm['id'], $mauticForm['name']);
                }

                $contentType = $parentObject->CType_labels[$row['CType']];

                $itemContent .= '<div class="panel panel-default">';
                $itemContent .= '<table class="table table-hover table-striped">';

                if (!empty($contentType)) {
                    $itemContent .= '<thead>';
                    $itemContent .= '<tr><th colspan="2">' . $parentObject->linkEditContent(htmlspecialchars($contentType), $row) . '</th></tr>';
                    $itemContent .= '</thead>';
                }

                $itemContent .= '<tbody>';
                $itemContent .= sprintf('<tr><td>ID</td><td>%s</td></tr>', $mauticForm['id']);
                $itemContent .= sprintf('<tr><td>Title</td><td>%s</td></tr>', htmlspecialchars($mauticForm['name']));
                $itemContent .= sprintf('<tr><td>Type</td><td>%s</td></tr>', htmlspecialchars($mauticForm['formType']));
                $itemContent .= sprintf('<tr><td>Published</td><td>%s</td></tr>', $mauticForm['isPublished'] ? 'yes' : 'no');
                $itemContent .= sprintf('<tr><td>Field Count</td><td>%s</td></tr>', count($mauticForm['fields']));
                $itemContent .= sprintf('<tr><td>Kiosk Mode</td><td>%s</td></tr>', $mauticForm['inKioskMode'] ? 'yes' : 'no');
                $itemContent .= '</tbody>';

                $itemContent .= '</table>';
                $itemContent .= '</div>';
            }

            $drawItem = false;
        }
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
