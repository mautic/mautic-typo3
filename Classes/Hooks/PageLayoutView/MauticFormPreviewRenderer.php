<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Hooks\PageLayoutView;

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
            $mauticForm = GeneralUtility::makeInstance(FormRepository::class)->getForm((int)$row['mautic_form_id']);

            if (empty($mauticForm)) {
                $this->getFormNotFoundContent((int)$row['mautic_form_id'], $itemContent);
            } else {
                if ($mauticForm['isPublished'] === false) {
                    $this->getFormNotPublishedContent((int)$mauticForm['id'], $mauticForm['name'], $itemContent);
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

    protected function getFormNotFoundContent(int $formId, string &$itemContent): string
    {
        $itemContent .= '<div class="alert alert-danger">';
        $itemContent .= '<div class="media">';
        $itemContent .= '<div class="media-left"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-info fa-stack-1x"></i></span></div>';
        $itemContent .= '<div class="media-body">';
        $itemContent .= '<h4 class="alert-title">' . sprintf('Mautic form with ID %s not found', $formId) . '</h4>';
        $itemContent .= '<p class="alert-message">Form does not exist or there is no connection to your mautic instance.</p>';
        $itemContent .= '</div>';
        $itemContent .= '</div>';
        $itemContent .= '</div>';

        return $itemContent;
    }

    protected function getFormNotPublishedContent(int $formId, string $formTitle, string &$itemContent): string
    {
        $itemContent .= '<div class="alert alert-warning">';
        $itemContent .= '<div class="media">';
        $itemContent .= '<div class="media-left"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-info fa-stack-1x"></i></span></div>';
        $itemContent .= '<div class="media-body">';
        $itemContent .= '<h4 class="alert-title">Mautic form is not published</h4>';
        $itemContent .= '<p class="alert-message">' . sprintf('Mautic form with <strong>ID %s</strong> (%s) is not published.', $formId, htmlspecialchars($formTitle)) . '</p>';
        $itemContent .= '</div>';
        $itemContent .= '</div>';
        $itemContent .= '</div>';

        return $itemContent;
    }
}
