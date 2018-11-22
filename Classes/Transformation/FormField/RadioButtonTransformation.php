<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Transformation\FormField;

use Bitmotion\Mautic\Transformation\FormField\Prototype\ListTransformationPrototype;

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
    /**
     * @var string
     */
    protected $type = 'radiogrp';

    /**
     * @var string
     */
    protected $listIdentifier = 'optionlist';

    /**
     * @var int
     */
    protected $multiple = 0;

    /**
     * @var bool
     */
    protected $updateCustomFieldsProperties = true;
}
