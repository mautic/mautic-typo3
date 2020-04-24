<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Transformation\FormField;

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
