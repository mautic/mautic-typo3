<?php

declare(strict_types=1);

/*
 * This extension was developed by Beech.it
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Mautic\Mautic\ViewHelpers\Form;

use Mautic\Mautic\Service\MauticService;
use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper;

/**
 * Class MauticPropertiesViewHelper.
 */
class MauticPropertiesViewHelper extends SelectViewHelper
{
    private $mauticService;

    /**
     * MauticPropertiesViewHelper constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mauticService = new MauticService();
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        if (!$this->mauticService->checkConfigPresent()) {
            $options[''] = 'Extension configuration is incomplete';

            return $options;
        }

        $options[''] = 'None';

        $api = $this->mauticService->createMauticApi('contactFields');

        $personFields = $api->getList();

        if(!is_array($personFields['fields'])){
            $options[''] = 'Extension configuration is incorrect';

            return $options;
        }

        foreach ($personFields['fields'] as $field) {
            $options[$field['alias']] = $field['label'];
        }

        return $options;
    }
}
