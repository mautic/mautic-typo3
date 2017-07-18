<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\MauticTypo3\Domain\Finishers;

use Mautic\MauticTypo3\Service\MauticService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;


class MauticContactFinisher extends AbstractFinisher
{

    private $mauticService;

    /**
     * MauticContactFinisher constructor.
     */
    public function __construct()
    {
        $this->mauticService = new MauticService();
    }

    protected function executeInternal()
    {
        // TODO
        $contactApi = $this->mauticService->createMauticApi('contacts');

        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition();

        DebuggerUtility::var_dump($formDefinition);

    }

}
