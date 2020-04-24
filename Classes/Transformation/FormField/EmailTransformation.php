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
