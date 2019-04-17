<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Form\FormDataProvider;

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
        if ('tt_content' !== $result['tableName'] || 'mautic_form' !== $result['recordTypeValue']) {
            return $result;
        }

        foreach ($this->formRepository->getAllForms() as $mauticForm) {
            $result['processedTca']['columns']['mautic_form_id']['config']['items'][] = [
                $mauticForm['name'],
                $mauticForm['id'],
                'content-form',
            ];
        }

        return $result;
    }
}
