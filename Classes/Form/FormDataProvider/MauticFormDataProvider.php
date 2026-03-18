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

namespace Leuchtfeuer\Mautic\Form\FormDataProvider;

use Leuchtfeuer\Mautic\Domain\Repository\FormRepository;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticFormDataProvider implements FormDataProviderInterface
{
    protected object $formRepository;

    public function __construct()
    {
        $this->formRepository = GeneralUtility::makeInstance(FormRepository::class);
    }

    #[\Override]
    public function addData(array $result): array
    {
        if ($result['tableName'] === 'tt_content' && $result['recordTypeValue'] === 'mautic_form') {
            foreach ($this->formRepository->getAllForms() as $mauticForm) {
                $result['processedTca']['columns']['mautic_form_id']['config']['items'][] = [
                    'label' => $mauticForm['name'],
                    'value' => $mauticForm['id'],
                ];
            }
        }

        return $result;
    }
}
