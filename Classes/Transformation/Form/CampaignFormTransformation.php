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

namespace Leuchtfeuer\Mautic\Transformation\Form;

/**
 * {
 *   "isPublished": true,
 *   "dateAdded": "2018-10-08T12:52:28+00:00",
 *   "dateModified": "2018-10-08T13:19:42+00:00",
 *   "createdBy": 6,
 *   "createdByUser": "Joe Doe",
 *   "modifiedBy": 6,
 *   "modifiedByUser": "Joe Doe",
 *   "id": 49,
 *   "name": "Kampagnenform",
 *   "alias": "kampagnen",
 *   "category": null,
 *   "description": null,
 *   "cachedHtml": "",
 *   "publishUp": null,
 *   "publishDown": null,
 *   "actions": [],
 *   "template": null,
 *   "inKioskMode": false,
 *   "renderStyle": true,
 *   "formType": "campaign",
 *   "postAction": "return",
 *   "postActionProperty": null,
 *   "fields": []
 * }
 */
class CampaignFormTransformation extends AbstractFormTransformation
{
    protected string $formType = 'campaign';
}
