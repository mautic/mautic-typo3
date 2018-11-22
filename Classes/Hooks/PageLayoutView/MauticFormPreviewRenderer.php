<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Hooks\PageLayoutView;

use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;

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
    public function preProcess(PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
        if ($row['CType'] === 'mautic_form') {
            $contentType = $parentObject->CType_labels[$row['CType']];
            if (!empty($contentType)) {
                $itemContent .= $parentObject->linkEditContent('<strong>' . htmlspecialchars($contentType) . '</strong>', $row) . '<br />';
            }

            $itemContent .= '<p>[Mautic Form ID = ' . $row['mautic_form_id'] . ']</p>';

            $drawItem = false;
        }
    }
}
