<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Transformation\FormField;

use Bitmotion\Mautic\Transformation\FormField\Prototype\ListTransformationPrototype;

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
    protected $type = 'select';

    protected $listIdentifier = 'list';

    protected $multiple = 0;
}
