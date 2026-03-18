<?php

use Leuchtfeuer\Mautic\Hooks\MauticTrackingHook;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Leuchtfeuer\MarketingAutomation\Dispatcher\Dispatcher;
use Leuchtfeuer\Mautic\Slot\MauticSubscriber;
use Leuchtfeuer\Mautic\Hooks\MauticTagHook;
use Leuchtfeuer\Mautic\Hooks\TCEmainHook;
use Leuchtfeuer\Mautic\Form\FormDataProvider\MauticFormDataProvider;
use TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use Leuchtfeuer\Mautic\FormEngine\FieldControl\UpdateSegmentsControl;
use Leuchtfeuer\Mautic\FormEngine\FieldControl\UpdateTagsControl;
use TYPO3\CMS\Core\Resource\Driver\DriverRegistry;
use Leuchtfeuer\Mautic\Driver\AssetDriver;
use TYPO3\CMS\Core\Resource\Index\ExtractorRegistry;
use Leuchtfeuer\Mautic\Index\Extractor;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Leuchtfeuer\Mautic\Controller\FrontendController;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use Leuchtfeuer\Mautic\Transformation\Form\CampaignFormTransformation;
use Leuchtfeuer\Mautic\Transformation\Form\StandaloneFormTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\CheckboxTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\DatetimeTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\EmailTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\HiddenTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\MultiCheckboxTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\MultiSelectTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\NumberTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\RadioButtonTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\SingleSelectTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\TelephoneTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\TextTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\TextareaTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\UrlTransformation;
use Leuchtfeuer\Mautic\Transformation\FormField\CountryListTransformation;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\NullWriter;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
defined('TYPO3') || die;

call_user_func(function (): void {
    if (Environment::isComposerMode() === false) {
        $filePath = ExtensionManagementUtility::extPath('mautic') . 'Libraries/vendor/autoload.php';
        if (@file_exists($filePath)) {
            require_once $filePath;
        } else {
            throw new \Exception(sprintf('File %s does not exist. Dependencies could not be loaded.', $filePath), 7049493518);
        }
    }

    if (ExtensionManagementUtility::isLoaded('marketing_automation') === false) {
        throw new \Exception('Required extension is not loaded: EXT:marketing_automation.', 7616907311);
    }

    $marketingDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
    $marketingDispatcher->addSubscriber(MauticSubscriber::class);

    ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mautic/Configuration/PageTS/Mod/Wizards/NewContentElement.tsconfig">'
    );

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['mautic_tag'] =
        MauticTagHook::class . '->setTags';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['mautic'] =
       MauticTrackingHook::class . '->addTrackingCode';

    // Register DataHandler hook for tag creation
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
        TCEmainHook::class;

    //##################
    //       FORM      #
    //##################
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][MauticFormDataProvider::class] = [
        'depends' => [
            DatabaseRowDefaultValues::class,
        ],
        'before' => [
            TcaSelectItems::class,
        ],
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1530047235] = [
        'nodeName' => 'updateSegmentsControl',
        'priority' => 30,
        'class' => UpdateSegmentsControl::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1551778913] = [
        'nodeName' => 'updateTagsControl',
        'priority' => 30,
        'class' => UpdateTagsControl::class,
    ];

    //#################
    //   FAL DRIVER   #
    //#################
    $driverRegistry = GeneralUtility::makeInstance(DriverRegistry::class);
    $driverRegistry->registerDriverClass(
        AssetDriver::class,
        AssetDriver::DRIVER_SHORT_NAME,
        AssetDriver::DRIVER_NAME,
        'FILE:EXT:mautic/Configuration/FlexForm/AssetDriver.xml'
    );

    //#################
    //   EXTRACTOR    #
    //#################
    GeneralUtility::makeInstance(ExtractorRegistry::class)->registerExtractionService(Extractor::class);

    //##################
    //      PLUGIN     #
    //##################
    ExtensionUtility::configurePlugin(
        'Mautic',
        'Form',
        [FrontendController::class => 'form'],
        [FrontendController::class => 'form'],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    //##################
    //      ICONS      #
    //##################
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    $icons = [
        'tx_mautic-mautic-icon' => 'EXT:mautic/Resources/Public/Icons/Extension.svg',
    ];

    foreach ($icons as $identifier => $source) {
        $iconRegistry->registerIcon(
            $identifier,
            SvgIconProvider::class,
            ['source' => $source]
        );
    }

    //######################
    //##    TYPOSCRIPT    ##
    //######################

    ExtensionManagementUtility::addTypoScript(
        'mautic',
        'constants',
        "@import 'EXT:mautic/Configuration/TypoScript/constants.typoscript'",
    );

    ExtensionManagementUtility::addTypoScript(
        'mautic',
        'setup',
        "@import 'EXT:mautic/Configuration/TypoScript/setup.typoscript'",
    );


    //##################
    //     EXTCONF     #
    //##################
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'] = [
            'transformation' => [
                'form' => [],
                'formField' => [],
            ],
        ];
    }

    //######################
    // FORM TRANSFORMATION #
    //######################
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['form']['mautic_finisher_campaign_prototype'] = CampaignFormTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['form']['mautic_finisher_standalone_prototype'] = StandaloneFormTransformation::class;

    //#######################
    // FIELD TRANSFORMATION #
    //#######################
    /**
     * // @extensionScannerIgnoreLine if for the ['transformation']
     */
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['AdvancedPassword'] = IgnoreTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Checkbox'] = CheckboxTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['ContentElement'] = IgnoreTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Date'] = DatetimeTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['DatePicker'] = DatetimeTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Email'] = EmailTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['GridRow'] = IgnoreTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Fieldset'] = IgnoreTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['FileUpload'] = IgnoreTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Hidden'] = HiddenTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['ImageUpload'] = IgnoreTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['MultiCheckbox'] = MultiCheckboxTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['MultiSelect'] = MultiSelectTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Number'] = NumberTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Page'] = IgnoreTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Password'] = IgnoreTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['RadioButton'] = RadioButtonTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['SingleSelect'] = SingleSelectTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['StaticText'] = IgnoreTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['SummaryPage'] = IgnoreTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Telephone'] = TelephoneTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Text'] = TextTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Textarea'] = TextareaTransformation::class;
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Url'] = UrlTransformation::class;

    // Register custom field transformation classes
    // @extensionScannerIgnoreLine
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['CountryList'] = CountryListTransformation::class;

    //##################
    //     LOGGING     #
    //##################
    // Turn logging off by default
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Leuchtfeuer']['Mautic'] = [
        'writerConfiguration' => [
            LogLevel::DEBUG => [
                NullWriter::class => [],
            ],
        ],
    ];

    if (Environment::getContext()->isDevelopment()) {
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['Leuchtfeuer']['Mautic'] = [
            'writerConfiguration' => [
                LogLevel::DEBUG => [
                    FileWriter::class => [
                        'logFileInfix' => 'mautic',
                    ],
                ],
            ],
        ];
    }
});
