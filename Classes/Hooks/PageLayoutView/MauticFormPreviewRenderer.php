<?php

declare(strict_types=1);

namespace Mautic\Mautic\Hooks\PageLayoutView;

/*
 * This file is part of the package mautic/mautic.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 *
 * (c) 2018 Josua Vogel <josua.vogel@telekom.de>, T-Systems Multimedia Solutions GmbH
 *
 */

use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;

class MauticFormPreviewRenderer implements PageLayoutViewDrawItemHookInterface
{
    /**
     * Preprocesses the preview rendering of a content element of type "Dynamic Content".
     *
     * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject  Calling parent object
     * @param bool                                   $drawItem      Whether to draw the item using the default functionality
     * @param string                                 $headerContent Header content
     * @param string                                 $itemContent   Item content
     * @param array                                  $row           Record row of tt_content
     */
    public function preProcess(
        PageLayoutView &$parentObject,
        &$drawItem,
        &$headerContent,
        &$itemContent,
        array &$row
    ) {
        if ($row['CType'] === 'mautic_form') {
            $itemContent .= '<p>[Mautic Form ID = '.$row['mautic_form_id'].']</p>';

            $drawItem = false;
        }
    }
}
