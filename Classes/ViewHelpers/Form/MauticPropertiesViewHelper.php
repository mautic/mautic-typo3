<?php
declare (strict_types = 1);

namespace BeechIt\Mautic\ViewHelpers\Form;

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper;
use BeechIt\Mautic\Service\MauticService;

/**
 * Class MauticPropertiesViewHelper
 */
class MauticPropertiesViewHelper extends SelectViewHelper
{
    private $mauticService;

    /**
     * MauticPropertiesViewHelper constructor.
     */
    function __construct()
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
        $options[''] = 'None';

        $api = $this->mauticService->createMauticApi('contactFields');

        $personFields = $api->getList();

        foreach ($personFields['fields'] as $field) {

            $options[$field['alias']] = $field['label'];

        }

        return $options;
    }
}
