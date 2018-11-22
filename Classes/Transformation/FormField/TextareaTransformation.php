<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Transformation\FormField;

/**
 * {
 *   "id": 225,
 *   "label": "textbereich",
 *   "showLabel": true,
 *   "alias": "textbereich",
 *   "type": "textarea",
 *   "defaultValue": null,
 *   "isRequired": false,
 *   "validationMessage": null,
 *   "helpMessage": null,
 *   "order": 19,
 *   "properties": [],
 *   "labelAttributes": null,
 *   "inputAttributes": null,
 *   "containerAttributes": null,
 *   "leadField": null,
 *   "saveResult": true,
 *   "isAutoFill": false
 * }
 */
class TextareaTransformation extends AbstractFormFieldTransformation
{
    /**
     * @var string
     */
    protected $type = 'textarea';
}
