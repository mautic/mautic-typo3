<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Transformation\FormField;

/**
 * {
 *   "id": 227,
 *   "label": "versteckt",
 *   "showLabel": true,
 *   "alias": "versteckt",
 *   "type": "hidden",
 *   "defaultValue": null,
 *   "isRequired": false,
 *   "validationMessage": null,
 *   "helpMessage": null,
 *   "order": 21,
 *   "properties": [],
 *   "labelAttributes": null,
 *   "inputAttributes": null,
 *   "containerAttributes": null,
 *   "leadField": null,
 *   "saveResult": true,
 *   "isAutoFill": false
 * }
 */
class HiddenTransformation extends AbstractFormFieldTransformation
{
    protected $type = 'hidden';
}
