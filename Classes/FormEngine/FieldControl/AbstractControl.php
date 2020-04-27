<?php

namespace Bitmotion\Mautic\FormEngine\FieldControl;

use TYPO3\CMS\Backend\Form\AbstractNode;

abstract class AbstractControl extends AbstractNode
{
    protected $tableName;

    protected $action;

    public function render()
    {
        return [
            'iconIdentifier' => 'actions-refresh',
            'title' => 'updateTagsControl',
            'linkAttributes' => [
                'onClick' => $this->getOnClickJS(),
                'href' => '#',
            ],
        ];
    }

    protected function getOnClickJS(): string
    {
        return <<<JS
require(['TYPO3/CMS/Backend/FormEngine', 'TYPO3/CMS/Backend/Modal'], function (FormEngine, Modal) {
    Modal.confirm(
        TYPO3.lang["FormEngine.refreshRequiredTitle"],
        TYPO3.lang["FormEngine.refreshRequiredContent"]
    ).on("button.clicked", function(event) {
        if (event.target.name === "ok") {
            let input = document.createElement("input");
            input.type = "hidden";
            input.name = "{$this->tableName}[{$this->action}]";
            input.value = '1';
            document.getElementsByName(FormEngine.formName).item(0).appendChild(input);
            FormEngine.saveDocument();
        }
        Modal.dismiss();
    });
return false;});
JS;
    }
}
