<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Form\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManagerInterface;

/**
 * @todo
 */
class MauticFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var FormPersistenceManagerInterface
     */
    protected $formPersistenceManager;

    public function __construct(FormPersistenceManagerInterface $formPersistenceManager = null)
    {
        if ($formPersistenceManager === null) {
            $formPersistenceManager = GeneralUtility::makeInstance(ObjectManager::class)->get(FormPersistenceManagerInterface::class);
        }

        $this->formPersistenceManager = $formPersistenceManager;
    }

    public function addData(array $result): array
    {
        if ('tt_content' !== $result['tableName'] || 'mautic_form' !== $result['recordTypeValue']) {
            return $result;
        }

        foreach ($this->formPersistenceManager->listForms() as $formConfiguration) {
            $invalidFormDefinition = $formConfiguration['invalid'] ?? false;
            $hasDeprecatedFileExtension = $formConfiguration['deprecatedFileExtension'] ?? false;

            if ($invalidFormDefinition || $hasDeprecatedFileExtension) {
                continue;
            }

            $form = $this->formPersistenceManager->load($formConfiguration['persistenceIdentifier']);
            if (!empty($form['renderingOptions']['mauticId'])) {
                $result['processedTca']['columns']['mautic_form_id']['config']['items'][] = [
                    $formConfiguration['name'] . ' (' . $formConfiguration['persistenceIdentifier'] . ')',
                    $form['renderingOptions']['mauticId'],
                    'content-form',
                ];
            }
        }

        return $result;
    }
}
