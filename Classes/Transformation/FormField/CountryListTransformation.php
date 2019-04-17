<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Transformation\FormField;

/**
 * {
 *   "id": 218,
 *   "label": "land",
 *   "showLabel": true,
 *   "alias": "land",
 *   "type": "country",
 *   "defaultValue": null,
 *   "isRequired": false,
 *   "validationMessage": null,
 *   "helpMessage": null,
 *   "order": 14,
 *   "properties": {
 *     "empty_value": null,
 *     "multiple": 0
 *   },
 *   "labelAttributes": null,
 *   "inputAttributes": null,
 *   "containerAttributes": null,
 *   "leadField": "country",
 *   "saveResult": true,
 *   "isAutoFill": false
 * }
 */
class CountryListTransformation extends AbstractFormFieldTransformation
{
    protected $type = 'country';
}
