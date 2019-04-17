<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Transformation\FormField;

/**
 * {
 *   "id": 216,
 *   "label": "mail",
 *   "showLabel": true,
 *   "alias": "mail",
 *   "type": "email",
 *   "defaultValue": null,
 *   "isRequired": false,
 *   "validationMessage": null,
 *   "helpMessage": null,
 *   "order": 9,
 *   "properties": {
 *     "placeholder": null
 *   },
 *   "labelAttributes": null,
 *   "inputAttributes": null,
 *   "containerAttributes": null,
 *   "leadField": "email",
 *   "saveResult": true,
 *   "isAutoFill": false
 * }
 */
class EmailTransformation extends AbstractFormFieldTransformation
{
    protected $type = 'email';
}
