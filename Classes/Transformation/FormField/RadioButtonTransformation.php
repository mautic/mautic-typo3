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

namespace Leuchtfeuer\Mautic\Transformation\FormField;

use Leuchtfeuer\Mautic\Transformation\FormField\Prototype\ListTransformationPrototype;

/**
 * {
 *   "id": 221,
 *   "label": "radio",
 *   "showLabel": true,
 *   "alias": "radio",
 *   "type": "radiogrp",
 *   "defaultValue": null,
 *   "isRequired": false,
 *   "validationMessage": null,
 *   "helpMessage": null,
 *   "order": 15,
 *   "properties": {
 *     "syncList": 0,
 *     "optionlist": {
 *       "list": [
 *         {
 *           "label": "label",
 *           "value": 1
 *         }
 *       ]
 *     },
 *     "labelAttributes": null
 *   },
 *   "labelAttributes": null,
 *   "inputAttributes": null,
 *   "containerAttributes": null,
 *   "leadField": null,
 *   "saveResult": true,
 *   "isAutoFill": false
 * }
 */
class RadioButtonTransformation extends ListTransformationPrototype
{
    protected string $type = 'radiogrp';

    protected string $listIdentifier = 'optionlist';

    protected int $multiple = 0;

    protected bool $updateCustomFieldsProperties = true;
}
