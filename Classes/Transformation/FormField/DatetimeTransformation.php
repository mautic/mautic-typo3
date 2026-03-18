<?php

declare(strict_types=1);

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\Transformation\FormField;

/**
 * {
 *   "id": 225,
 *   "label": "textbereich",
 *   "showLabel": true,
 *   "alias": "textbereich",
 *   "type": "url",
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
class DatetimeTransformation extends AbstractFormFieldTransformation
{
    protected string $type = 'datetime';
}
