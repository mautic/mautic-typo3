<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\ViewHelpers\Form;

use Bitmotion\Mautic\Domain\Repository\FieldRepository;
use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper;
use TYPO3\CMS\Lang\LanguageService;

class MauticPropertiesViewHelper extends SelectViewHelper
{
    /**
     * @var FieldRepository
     */
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
            $options[$field['alias']] = $contactsLang . ': ' . $field['label'];
        }

        asort($options);

//        TODO: Support companies
//        foreach ($companyFields as $field) {
//            $options[$field['alias']] = $companiesLang . ': ' . $field['label'];
//        }

        return $options;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
