<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\FormEngine\FieldControl;

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

use TYPO3\CMS\Backend\Form\AbstractNode;

class UpdateSegmentsControl extends AbstractNode
{
    public function render(): array
    {
        $onClick = 'top.TYPO3.Modal.confirm('
            . 'TYPO3.lang["FormEngine.refreshRequiredTitle"],'
            . 'TYPO3.lang["FormEngine.refreshRequiredContent"]'
            . ')'
            . '.on('
            . '"button.clicked",'
            . 'function(e) {'
            . 'if (e.target.name == "ok" && TBE_EDITOR.checkSubmit(-1)) {'
            . 'var input = document.createElement("input");'
            . 'input.type = "hidden";'
            . 'input.name = "tx_marketingautomation_segments[updateSegments]";'
            . 'input.value = 1;'
            . 'document.getElementsByName(TBE_EDITOR.formname).item(0).appendChild(input);'
            . 'TBE_EDITOR.submitForm();'
            . '}'
            . 'top.TYPO3.Modal.dismiss();'
            . '}'
            . ');'
            . 'return false;';

        return [
            'iconIdentifier' => 'actions-refresh',
            'title' => 'updateSegmentsControl',
            'linkAttributes' => [
                'onClick' => $onClick,
                'href' => '#',
            ],
        ];
    }
}
