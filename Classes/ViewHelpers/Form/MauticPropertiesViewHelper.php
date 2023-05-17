<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\ViewHelpers\Form;

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

use Bitmotion\Mautic\Domain\Repository\FieldRepository;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper;

class MauticPropertiesViewHelper extends SelectViewHelper
{
    protected $fieldRepository;

    public function __construct(FieldRepository $fieldRepository)
    {
        parent::__construct();

        $this->fieldRepository = $fieldRepository;
    }

    /**
     * Fills the form engine dropdown with all known Mautic contact and company field types
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();

        $contactFields = $this->fieldRepository->getContactFields();

//        TODO: Support companies
//        $companyFields = $this->companyRepository->findCompanyFields();

        $languageService = $this->getLanguageService();
        $contactsLang = $languageService->sL('LLL:EXT:mautic/Resources/Private/Language/locallang_tca.xlf:mautic.contact');
//        TODO: Support companies
//        $companiesLang = $languageService->sL('LLL:EXT:mautic/Resources/Private/Language/locallang_tca.xlf:mautic.company');

        foreach ($contactFields as $field) {
            $options[$field['alias']] = sprintf('%s: %s |||%s|||', $contactsLang, $field['label'], $field['type']);
        }

        asort($options);

//        TODO: Support companies
//        foreach ($companyFields as $field) {
//            $options[$field['alias']] = $companiesLang . ': ' . $field['label'];
//        }

        return $options;
    }

    protected function renderOptionTag($value, $label, $isSelected)
    {
        $output = '<option value="' . htmlspecialchars($value) . '"';
        if ($isSelected) {
            $output .= ' selected="selected"';
        }

        $matches = preg_match('/\|\|\|(.*)\|\|\|/m', $label, $dataType);

        if ($matches === 1) {
            $label = str_replace($dataType[0], '', $label);
            $output .= ' data-type="' . htmlspecialchars($dataType[1]) . '">' . htmlspecialchars($label) . '</option>';
        } else {
            $output .= '>' . htmlspecialchars($label) . '</option>';
        }

        return $output;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
