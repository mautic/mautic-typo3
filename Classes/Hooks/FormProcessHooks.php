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

namespace Mautic\Mautic\Hooks;

use Mautic\Mautic\Service\MauticService;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManager;

class FormProcessHooks
{
    private $mauticService;

    /**
     * FormProcessHooks constructor.
     */
    public function __construct()
    {
        $this->mauticService = new MauticService();
    }

    /**
     * @param string $formPersistenceIdentifier
     * @param array  $formDefinition
     *
     * @return array
     */
    public function beforeFormCreate(string $formPersistenceIdentifier, array $formDefinition): array
    {
        // Check if this form is a Mautic form, if not then simply skip Mautic
        if ($formDefinition['prototypeName'] !== 'mautic_finisher_campaign_prototype' && $formDefinition['prototypeName'] !== 'mautic_finisher_standalone_prototype') {
            return $formDefinition;
        }
        if ($this->mauticService->checkConfigPresent() === false) {
            return $formDefinition;
        }
        // Create the Mautic Api
        $formApi = $this->mauticService->createMauticApi('forms');

        // Transform the form data to a format mautic can process
        $data = $this->convertFormStructure($formDefinition);

        // API call
        $form = $formApi->create($data);

        // Save the form id of Mautic in the YAML
        $formDefinition['renderingOptions']['mauticId'] = $form['form']['id'];

        $formDefinition = $this->matchMauticAliasToTypo($formDefinition, $form);

        // Return to let the process continue as normal
        return $formDefinition;
    }

    /**
     * @param string $formPersistenceIdentifier
     * @param array  $formDefinition
     *
     * @return array
     */
    public function beforeFormSave(string $formPersistenceIdentifier, array $formDefinition): array
    {
        $persistenceManager = GeneralUtility::makeInstance(ObjectManager::class)->get(FormPersistenceManager::class);
        $configuration      = $persistenceManager->load($formPersistenceIdentifier);

        // Check if the Mautic ID is set, if not, skip Mautic completely
        if (empty($configuration['renderingOptions']['mauticId'])) {
            return $formDefinition;
        }

        $formApi = $this->mauticService->createMauticApi('forms');

        // Transform the form data to a format mautic can process
        $data = $this->convertFormStructure($formDefinition);

        // API call
        $form = $formApi->edit($configuration['renderingOptions']['mauticId'], $data, true);

        // Just to make sure...
        $formDefinition['renderingOptions']['mauticId'] = $form['form']['id'];

        $formDefinition = $this->matchMauticAliasToTypo($formDefinition, $form);

        // Return to let the process continue as normal
        return $formDefinition;
    }

    /**
     * @param string $formPersistenceIdentifier
     * @param array  $formDefinition
     *
     * @return array
     */
    public function beforeFormDuplicate(string $formPersistenceIdentifier, array $formDefinition): array
    {
        // Check if the Mautic ID is set, if not, skip Mautic completely
        if (empty($formDefinition['renderingOptions']['mauticId'])) {
            return $formDefinition;
        }

        $formApi = $this->mauticService->createMauticApi('forms');

        // Transform the form data to a format mautic can process
        $data = $this->convertFormStructure($formDefinition);

        // API call
        $form = $formApi->create($data);

        // Save the form id of Mautic in the YAML
        $formDefinition['renderingOptions']['mauticId'] = $form['form']['id'];

        $formDefinition = $this->matchMauticAliasToTypo($formDefinition, $form);

        // Return to let the process continue as normal
        return $formDefinition;
    }

    /**
     * @param string $formPersistenceIdentifier
     */
    public function beforeFormDelete(string $formPersistenceIdentifier): string
    {
        $persistenceManager = GeneralUtility::makeInstance(ObjectManager::class)->get(FormPersistenceManager::class);
        $configuration      = $persistenceManager->load($formPersistenceIdentifier);

        // Check if the Mautic ID is set, if not, skip Mautic completely
        if (empty($configuration['renderingOptions']['mauticId'])) {
            return $formPersistenceIdentifier;
        }

        $formApi = $this->mauticService->createMauticApi('forms');

        // API call to delete the form in Mautic
        $formApi->delete($configuration['renderingOptions']['mauticId']);

        // Return to let the process continue as normal
        return $formPersistenceIdentifier;
    }

    /**
     * @param array $formDefinition
     *
     * @return array
     */
    private function convertFormStructure(array $formDefinition): array
    {

        // Instantiate the array for the form
        $returnFormStructure = [];
        // Set the form name and alias
        $returnFormStructure['name']        = $formDefinition['label'];
        $returnFormStructure['alias']       = $formDefinition['identifier'];
        $returnFormStructure['isPublished'] = true;
        // Set the form type accroding to the prototype name
        $returnFormStructure['formType'] = $this->getMauticFormType($formDefinition['prototypeName']);
        // Instantiate the array of fields
        $returnFormStructure['fields'] = [];
        // Set an int value that will be incremented per field processed to create a structured array key
        $formFieldIdentifier = 0;

        // For each page in the form (mautic does not know pages, so we discard them)
        foreach ((array) $formDefinition['renderables'] as $formPage) {

            // First loop to find any form fields that are stored within another form element
            foreach ((array) $formPage['renderables'] as $formElement) {
                // Check for container elements and eliminate them
                if ($formElement['type'] === 'Fieldset' || $formElement['type'] === 'GridRow') {
                    // Check for form fields in the container element
                    foreach ((array) $formElement['renderables'] as $containerElement) {
                        // Fieldset can contain GridRow and other way round
                        if ($containerElement['type'] === 'Fieldset' || $containerElement['type'] === 'GridRow') {
                            foreach ((array) $containerElement['renderables'] as $containerElementInner) {
                                // Add the form field to the form page so that we can process it normally
                                array_push($formPage['renderables'], $containerElementInner);
                            }
                        } else {
                            // Add the form field to the form page so that we can process it normally
                            array_push($formPage['renderables'], $containerElement);
                        }
                    }
                }
            }

            // For each form element on the page
            foreach ((array) $formPage['renderables'] as $formElement) {
                if (!empty($this->matchFormFieldTypes($formElement['type']))) {
                    // Instantiate an array for this particular for field
                    $formField = [];
                    // Set the label of the form field
                    $formField['label'] = $this->getFieldIden($formElement);
                    $formField['alias'] = str_replace('-', '_', $formElement['identifier']);

                    // Save formField ID if present
                    if (!empty($formElement['properties']['mauticFieldId'])) {
                        $formField['id'] = $formElement['properties']['mauticId'];
                    }
                    // Save formField alias if present
                    if (!empty($formElement['properties']['mauticAlias'])) {
                        $formField['alias'] = $formElement['properties']['mauticAlias'];
                    }
                    // Save formField leadfield if present
                    if (!empty($formElement['properties']['mauticTable'])) {
                        $formField['leadField'] = $formElement['properties']['mauticTable'];
                    }
                    // Save formField default value if present
                    if (!empty($formElement['defaultValue'])) {
                        $formField['defaultValue'] = $formElement['defaultValue'];
                    }
                    // Save formField placeholder if present
                    if (!empty($formElement['properties']['fluidAdditionalAttributes']['placeholder'])) {
                        $formField['properties']                = [];
                        $formField['properties']['placeholder'] = $formElement['properties']['fluidAdditionalAttributes']['placeholder'];
                    }

                    // Match the type of the form field to a type known in Mautic
                    $formField['type'] = $this->matchFormFieldTypes($formElement['type']);
                    // Set the required parameter if given
                    foreach ((array) $formElement['validators'] as $validator) {
                        if ($validator['identifier'] === 'NotEmpty') {
                            $formField['isRequired'] = true;
                        }
                    }

                    // If the form field has options (e.g. RadioButton or a CheckList)
                    if ($this->hasMultipleOptions($formElement['type'])) {

                        // For some reason Mautic likes to use optionlist and list, so we will have to deal with that
                        switch ($formElement['type']) {
                            case 'RadioButton':
                                $listIdentifier = 'optionlist';
                                break;
                            case 'SingleSelect':
                                $listIdentifier = 'list';
                                break;
                            case 'MultiSelect':
                                $listIdentifier = 'list';
                                break;
                            case 'MultiCheckbox':
                                $listIdentifier = 'optionlist';
                                break;
                            default:
                                $listIdentifier = 'list';
                        }

                        // Instantiate the needed arrays
                        $formField['properties']                          = [];
                        $formField['properties'][$listIdentifier]         = [];
                        $formField['properties'][$listIdentifier]['list'] = [];

                        // multiselect in mautic is a select field with multiple set to 1
                        if ($formElement['type'] === 'MultiSelect') {
                            $formField['properties']['multiple'] = 1;
                        }
                        // Set an int value that will be incremented per option processed to create a structured array key
                        $optionIdentifier = 0;

                        // For each available option
                        foreach ((array) $formElement['properties']['options'] as $key => $value) {
                            // Set the label and the value for the option
                            $formField['properties'][$listIdentifier]['list'][$optionIdentifier] = ['label' => $key, 'value' => $value];

                            ++$optionIdentifier;
                        }
                    }

                    // Add the field to the array of the form
                    $returnFormStructure['fields'][$formFieldIdentifier] = $formField;

                    ++$formFieldIdentifier;
                }
            }
        }

        // Return the generated structure
        return $returnFormStructure;
    }

    /**
     * @param string $formFieldType
     *
     * @return string
     */
    private function matchFormFieldTypes(string $formFieldType): string
    {
        // Get the array to match the field types
        $yaml = $this->getFormFieldConfig();

        // For each field type, check if it is this field type and return the Mautic equivalent
        foreach ($yaml as $fieldTypeNames) {
            if ($fieldTypeNames['TYPO3'] === $formFieldType) {
                $type = $fieldTypeNames['Mautic'];
            }
        }

        return $type ?? '';
    }

    /**
     * @param string $formFieldType
     *
     * @return bool
     */
    private function hasMultipleOptions(string $formFieldType): bool
    {
        // Get the array to match the field types
        $yaml = $this->getFormFieldConfig();

        // For each field type, check if it can have multiple options (e.g. radio buttons)
        foreach ($yaml as $fieldTypeNames) {
            if ($fieldTypeNames['TYPO3'] === $formFieldType) {
                if ($fieldTypeNames['HasMultipleAnswers'] == 'true') {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    private function getFormFieldConfig(): array
    {
        static $yaml;

        if ($yaml === null) {
            // Instantiate a class that can turn YAML files into a PHP array
            $fileLoader = GeneralUtility::makeInstance(YamlFileLoader::class);
            // Load the FieldTypes.yaml
            $yaml = $fileLoader->load('EXT:mautic/Configuration/Yaml/FieldTypes.yaml');
        }

        return $yaml;
    }

    /**
     * @param string $prototypeType
     *
     * @return string
     */
    private function getMauticFormType(string $prototypeType): string
    {
        // Check if the prototype is set as campaign form on standalone
        if ($prototypeType === 'mautic_finisher_campaign_prototype') {
            $type = 'campaign';
        } else {
            $type = 'standalone';
        }

        // Return the type, either standalone or campaign form
        return $type ?? '';
    }

    /**
     * @param array $typoForm
     * @param array $mauticForm
     *
     * @return array
     */
    private function matchMauticAliasToTypo(array $typoForm, array $mauticForm): array
    {
        // Match the TYPO3 fields with the Mautic fields and save the Mautic alias
        foreach ((array) $mauticForm['form']['fields'] as $mauticFormField) {

            // For each page in the TYPO3 form
            foreach ((array) $typoForm['renderables'] as $typoFormPageKey => $typoFormPage) {
                // For each element on the TYPO3 form page
                foreach ((array) $typoFormPage['renderables'] as $typoFormFieldKey => $typoFormField) {

                    $label = $this->getFieldIden($typoFormField);

                    // Check if element is a container element
                    if ($typoFormField['type'] === 'Fieldset' || $typoFormField['type'] === 'GridRow') {
                        // For each form field in the container'
                        foreach ((array) $typoFormField['renderables'] as $listFormFieldKey => $listFormField) {
                            // Fieldset can contain Gridrow and other way round
                            if ($listFormField['type'] === 'Fieldset' || $listFormField['type'] === 'GridRow') {
                                foreach ($listFormField['renderables'] as $listFormFieldInnerKey => $listFormFieldInner) {

                                    $label = $this->getFieldIden($listFormFieldInner);

                                    if ($label == $mauticFormField['label']) {
                                        // Set the Mautic Alias for the field
                                        $typoForm['renderables'][$typoFormPageKey]['renderables'][$typoFormFieldKey]['renderables'][$listFormFieldKey]['renderables'][$listFormFieldInnerKey]['properties']['mauticAlias'] = $mauticFormField['alias'];
                                        // Set the Mautic id for the field
                                        $typoForm['renderables'][$typoFormPageKey]['renderables'][$typoFormFieldKey]['renderables'][$listFormFieldKey]['renderables'][$listFormFieldInnerKey]['properties']['mauticFieldId'] = $mauticFormField['id'];
                                    } else {
                                        // Set the Mautic Alias for the field
                                        $typoForm['renderables'][$typoFormPageKey]['renderables'][$typoFormFieldKey]['renderables'][$listFormFieldKey]['properties']['mauticAlias'] = $mauticFormField['alias'];
                                        // Set the Mautic id for the field
                                        $typoForm['renderables'][$typoFormPageKey]['renderables'][$typoFormFieldKey]['renderables'][$listFormFieldKey]['properties']['mauticFieldId'] = $mauticFormField['id'];
                                    }
                                }
                            }

                            $label = $this->getFieldIden($listFormField);

                            // If exists in Mautic then save the needed properties
                            if ($label == $mauticFormField['label']) {
                                // Set the Mautic Alias for the field
                                $typoForm['renderables'][$typoFormPageKey]['renderables'][$typoFormFieldKey]['renderables'][$listFormFieldKey]['properties']['mauticAlias'] = $mauticFormField['alias'];
                                // Set the Mautic id for the field
                                $typoForm['renderables'][$typoFormPageKey]['renderables'][$typoFormFieldKey]['renderables'][$listFormFieldKey]['properties']['mauticFieldId'] = $mauticFormField['id'];
                            }
                        }
                        // If not a container element check if it exists in Mautic, then save the needed properties
                    } elseif ($typoFormField['label'] == $mauticFormField['label']) {
                        // Set the Mautic Alias for the field
                        $typoForm['renderables'][$typoFormPageKey]['renderables'][$typoFormFieldKey]['properties']['mauticAlias'] = $mauticFormField['alias'];
                        // Set the Mautic id for the field
                        $typoForm['renderables'][$typoFormPageKey]['renderables'][$typoFormFieldKey]['properties']['mauticFieldId'] = $mauticFormField['id'];
                    }
                }
            }
        }
        // Return the array
        return $typoForm;
    }

    private function getFieldIden(array $typoField) : string
    {
        if (!empty($typoField['label'])) {
            $label = $typoField['label'];
        } elseif (!empty($formDefinition['properties']['fluidAdditionalAttributes']['placeholder'])) {
            $label = $typoField['properties']['fluidAdditionalAttributes'];
        } else {
            $label = $typoField['identifier'];
        }

        return $label;
    }
}
