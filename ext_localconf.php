<?php
defined('TYPO3_MODE') || die;

call_user_func(function () {
    if (!defined('TYPO3_COMPOSER_MODE') || !TYPO3_COMPOSER_MODE) {
        require \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mautic') . 'Libraries/vendor/autoload.php';
    }

    $marketingDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Bitmotion\MarketingAutomation\Dispatcher\Dispatcher::class);
    $marketingDispatcher->addSubscriber(\Bitmotion\Mautic\Slot\MauticSubscriber::class);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mautic/Configuration/PageTS/Mod/Wizards/NewContentElement.tsconfig">'
    );

    ###################
    #      HOOKS      #
    ###################
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess']['mautic'] =
        \Bitmotion\Mautic\Slot\MauticSubscriber::class . '->setPreferredLocale';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['mautic'] =
        \Bitmotion\Mautic\Hooks\MauticTrackingHook::class . '->addTrackingCode';

    // Register for hook to show preview of tt_content element of CType="mautic_form" in page module
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['mautic_form'] =
        \Bitmotion\Mautic\Hooks\PageLayoutView\MauticFormPreviewRenderer::class;

    if (TYPO3_MODE === 'FE') {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postTransform']['mautic_tag'] =
            \Bitmotion\Mautic\Hooks\MauticTagHook::class . '->setTags';
    }

    ###################
    #       FORM      #
    ###################
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Bitmotion\Mautic\Form\FormDataProvider\MauticFormDataProvider::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues::class,
        ],
        'before' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class,
        ],
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1530047235] = [
        'nodeName' => 'updateSegmentsControl',
        'priority' => 30,
        'class' => \Bitmotion\Mautic\FormEngine\FieldControl\UpdateSegmentsControl::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1551778913] = [
        'nodeName' => 'updateTagsControl',
        'priority' => 30,
        'class' => \Bitmotion\Mautic\FormEngine\FieldControl\UpdateTagsControl::class,
    ];

    ##################
    #   FAL DRIVER   #
    ##################
    $driverRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Driver\DriverRegistry::class);
    $driverRegistry->registerDriverClass(
        \Bitmotion\Mautic\Driver\AssetDriver::class,
        \Bitmotion\Mautic\Driver\AssetDriver::DRIVER_SHORT_NAME,
        \Bitmotion\Mautic\Driver\AssetDriver::DRIVER_NAME,
        'FILE:EXT:mautic/Configuration/FlexForm/AssetDriver.xml'
    );

    ##################
    #   EXTRACTOR    #
    ##################
    \TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance()->registerExtractionService(\Bitmotion\Mautic\Index\Extractor::class);

    ###################
    #   SIGNALSLOTS   #
    ###################
    $slotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $slotDispatcher->connect(
        \TYPO3\CMS\Backend\Controller\EditDocumentController::class,
        'initAfter',
        \Bitmotion\Mautic\Slot\EditDocumentControllerSlot::class,
        'synchronizeSegments'
    );

    $slotDispatcher->connect(
        \TYPO3\CMS\Backend\Controller\EditDocumentController::class,
        'initAfter',
        \Bitmotion\Mautic\Slot\EditDocumentControllerSlot::class,
        'synchronizeTags'
    );

    $slotDispatcher->connect(
        \TYPO3\CMS\Core\Resource\Index\FileIndexRepository::class,
        'recordUpdated',
        \Bitmotion\Mautic\Slot\FileIndexRepository::class,
        'updateRecord'
    );

    $slotDispatcher->connect(
        \TYPO3\CMS\Core\Resource\Index\FileIndexRepository::class,
        'recordCreated',
        \Bitmotion\Mautic\Slot\FileIndexRepository::class,
        'createRecord'
    );

    $slotDispatcher->connect(
        \TYPO3\CMS\Core\Resource\Index\FileIndexRepository::class,
        'recordMarkedAsMissing',
        \Bitmotion\Mautic\Slot\FileIndexRepository::class,
        'markRecordAsMissing'
    );

    ###################
    #      PLUGIN     #
    ###################
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Bitmotion.mautic',
        'Form',
        ['Frontend' => 'form'],
        ['Frontend' => 'form'],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );


    ###################
    #      ICONS      #
    ###################
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $icons = [
        'tx_mautic-mautic-icon' => 'EXT:mautic/Resources/Public/Icons/mautic.png',
        'tx_mautic-mautic-blue-icon' => 'EXT:mautic/Resources/Public/Icons/mautic-with-background.png',
    ];

    foreach ($icons as $identifier => $source) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            ['source' => $source]
        );
    }


    ###################
    #     EXTCONF     #
    ###################
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'] = [
            'transformation' => [
                'form' => [],
                'formField' => [],
            ]
        ];
    }


    #######################
    # FORM TRANSFORMATION #
    #######################
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['form']['mautic_finisher_campaign_prototype'] = \Bitmotion\Mautic\Transformation\Form\CampaignFormTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['form']['mautic_finisher_standalone_prototype'] = \Bitmotion\Mautic\Transformation\Form\StandaloneFormTransformation::class;


    ########################
    # FIELD TRANSFORMATION #
    ########################
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Checkbox'] = \Bitmotion\Mautic\Transformation\FormField\CheckboxTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['ContentElement'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['GridRow'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Hidden'] = \Bitmotion\Mautic\Transformation\FormField\HiddenTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['MultiCheckbox'] = \Bitmotion\Mautic\Transformation\FormField\MultiCheckboxTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['MultiSelect'] = \Bitmotion\Mautic\Transformation\FormField\MultiSelectTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Page'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['RadioButton'] = \Bitmotion\Mautic\Transformation\FormField\RadioButtonTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['SingleSelect'] = \Bitmotion\Mautic\Transformation\FormField\SingleSelectTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['StaticText'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Text'] = \Bitmotion\Mautic\Transformation\FormField\TextTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Textarea'] = \Bitmotion\Mautic\Transformation\FormField\TextareaTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['SummaryPage'] = \Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation::class;

    // Register custom field transformation classes
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Email'] = \Bitmotion\Mautic\Transformation\FormField\EmailTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['CountryList'] = \Bitmotion\Mautic\Transformation\FormField\CountryListTransformation::class;


    ###################
    #     LOGGING     #
    ###################
    // Turn logging off by default
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Bitmotion']['Mautic'] = [
        'writerConfiguration' => [
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
                \TYPO3\CMS\Core\Log\Writer\NullWriter::class => [],
            ],
        ],
    ];

    if (\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isDevelopment()) {
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['Bitmotion']['Mautic'] = [
            'writerConfiguration' => [
                \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
                    \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                        'logFile' => 'typo3temp/logs/ext_mautic.log'
                    ],
                ],
            ],
        ];
    }

});
