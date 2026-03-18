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
 *   "id": 209,
 *   "label": "Ausw\u00e4hlen",
 *   "showLabel": true,
 *   "alias": "auswahlen",
 *   "type": "select",
 *   "defaultValue": null,
 *   "isRequired": false,
 *   "validationMessage": null,
 *   "helpMessage": null,
 *   "order": 1,
 *   "properties": {
 *     "syncList": 0,
 *     "list": {
 *       "list": [
 *         {
 *           "label": "label",
 *           "value": 1
 *         }
 *       ]
 *     },
 *     "empty_value": null,
 *     "multiple": 0
 *   },
 *   "labelAttributes": null,
 *   "inputAttributes": null,
 *   "containerAttributes": null,
 *   "leadField": null,
 *   "saveResult": true,
 *   "isAutoFill": false
 * }
 */
class SingleSelectTransformation extends ListTransformationPrototype
{
    protected string $type = 'select';

    protected string $listIdentifier = 'list';

    protected int $multiple = 0;
}
