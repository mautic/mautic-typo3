<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\FormEngine\FieldControl;

use TYPO3\CMS\Backend\Form\AbstractNode;

class UpdateTagsControl extends AbstractNode
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
            . 'input.name = "tx_mautic_domain_model_tag[updateTags]";'
            . 'input.value = 1;'
            . 'document.getElementsByName(TBE_EDITOR.formname).item(0).appendChild(input);'
            . 'TBE_EDITOR.submitForm();'
            . '}'
            . 'top.TYPO3.Modal.dismiss();'
            . '}'
            . ');'
            . 'return false;';

        $result = [
            'iconIdentifier' => 'actions-refresh',
            'title' => 'updateTagsControl',
            'linkAttributes' => [
                'onClick' => $onClick,
                'href' => '#',
            ],
        ];

        return $result;
    }
}
