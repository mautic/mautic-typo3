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
    protected $type = 'select';

    protected $listIdentifier = 'list';

    protected $multiple = 1;
}
