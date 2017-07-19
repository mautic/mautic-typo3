<?php

declare(strict_types=1);

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\MauticTypo3\ViewHelpers\Form;

use Mautic\MauticTypo3\Service\MauticService;
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

        foreach ($personFields['fields'] as $field) {
            $options[$field['alias']] = $field['label'];
        }

        return $options;
    }
}
