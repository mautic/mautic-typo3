<?php

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\FormEngine\FieldControl;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;

abstract class AbstractControl extends AbstractNode
{
    protected string $tableName;

    protected string $action;

    protected string $title;

    #[\Override]
    public function render(): array
    {
        return [
            'iconIdentifier' => 'actions-refresh',
            'title' => $this->title,
            'linkAttributes' => [
                'class' => 'mautic-refresh-control',
                'data-table-name' => $this->tableName,
                'data-action' => $this->action,
                'href' => '#',
            ],
            'javaScriptModules' => [
                JavaScriptModuleInstruction::create('@leuchtfeuer/mautic/FormEditor/refresh.js'),
            ],
        ];
    }
}
