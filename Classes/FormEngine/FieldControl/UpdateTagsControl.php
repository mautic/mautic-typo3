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
 *  (c) 2023 Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 *
 ***/

class UpdateTagsControl extends AbstractControl
{
    protected $tableName = 'tx_mautic_domain_model_tag';

    protected $action = 'updateTags';
}
