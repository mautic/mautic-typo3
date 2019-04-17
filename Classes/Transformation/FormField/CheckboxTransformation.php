<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Transformation\FormField;

use Bitmotion\Mautic\Transformation\FormField\Prototype\ListTransformationPrototype;

/**
 * {
 *   "id": 212,
 *   "label": "Checkboxgruppe",
 *   "showLabel": true,
 *   "alias": "checkboxgruppe",
 *   "type": "checkboxgrp",
 *   "defaultValue": null,
 *   "isRequired": false,
 *   "validationMessage": null,
 *   "helpMessage": null,
 *   "order": 5,
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
class CheckboxTransformation extends ListTransformationPrototype
{
    protected $type = 'checkboxgrp';

    protected $listIdentifier = 'optionlist';

    protected $multiple = 0;

    protected $updateCustomFieldsProperties = true;
}
