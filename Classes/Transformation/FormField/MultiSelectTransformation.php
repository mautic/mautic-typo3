<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Transformation\FormField;

use Bitmotion\Mautic\Transformation\FormField\Prototype\ListTransformationPrototype;

/**
 * {
 *   "id": 229,
 *   "label": "auswahl multi",
 *   "showLabel": true,
 *   "alias": "auswahl_multi",
 *   "type": "select",
 *   "defaultValue": null,
 *   "isRequired": false,
 *   "validationMessage": null,
 *   "helpMessage": null,
 *   "order": 2,
 *   "properties": {
 *     "syncList": 0,
 *     "list": {
 *       "list": [
 *         {
 *           "label": "label 1",
 *           "value": 1
 *         },
 *         {
 *           "label": "label 2",
 *           "value": 2
 *         }
 *       ]
 *     },
 *     "empty_value": null,
 *     "multiple": 1
 *   },
 *   "labelAttributes": null,
 *   "inputAttributes": null,
 *   "containerAttributes": null,
 *   "leadField": null,
 *   "saveResult": true,
 *   "isAutoFill": false
 * }
 */
class MultiSelectTransformation extends ListTransformationPrototype
{
    /**
     * @var string
     */
    protected $type = 'select';

    /**
     * @var string
     */
    protected $listIdentifier = 'list';

    /**
     * @var int
     */
    protected $multiple = 1;
}
