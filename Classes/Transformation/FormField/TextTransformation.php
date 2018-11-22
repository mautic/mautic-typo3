<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Transformation\FormField;

/**
 * {
 *   "id": 224,
 *   "label": "text",
 *   "showLabel": true,
 *   "alias": "text",
 *   "type": "text",
 *   "defaultValue": null,
 *   "isRequired": false,
 *   "validationMessage": null,
 *   "helpMessage": null,
 *   "order": 18,
 *   "properties": {
 *     "placeholder": null
 *   },
 *   "labelAttributes": null,
 *   "inputAttributes": null,
 *   "containerAttributes": null,
 *   "leadField": null,
 *   "saveResult": true,
 *   "isAutoFill": false
 * }
 */
class TextTransformation extends AbstractFormFieldTransformation
{
    /**
     * @var string
     */
    protected $type = 'text';
}
