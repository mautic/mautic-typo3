<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Form\FormDataProvider;

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

use Bitmotion\Mautic\Domain\Repository\FormRepository;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class MauticFormDataProvider implements FormDataProviderInterface
{
    protected $formRepository;

    public function __construct()
    {
        $this->formRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(FormRepository::class);
    }

    public function addData(array $result): array
    {
        if ($result['tableName'] === 'tt_content' && $result['recordTypeValue'] === 'mautic_form') {
            foreach ($this->formRepository->getAllForms() as $mauticForm) {
                $result['processedTca']['columns']['mautic_form_id']['config']['items'][] = [
                    $mauticForm['name'],
                    $mauticForm['id'],
                    'content-form',
                ];
            }
        }

        return $result;
    }
}
