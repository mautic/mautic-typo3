<?php

declare(strict_types=1);

namespace Bitmotion\Mautic\FormEngine\FieldControl;

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

class UpdateSegmentsControl extends AbstractControl
{
    protected $tableName = 'tx_marketingautomation_segments';

    protected $action = 'updateSegments';
}
