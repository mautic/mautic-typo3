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
    protected string $type = 'country';
}
