<?php

defined('TYPO3_MODE') || die();

/***************
 * Add Content Element
 */
$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['dynamic_content'] = 'content-special-div';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'LLL:EXT:mautic/Resources/Private/Language/Backend.xlf:content_element.dynamic_content',
        'dynamic_content',
        'content-special-div',
    ],
    '--div--',
    'after'
);

/***************
 * Configure element type
 */
if (!is_array($GLOBALS['TCA']['tt_content']['types']['dynamic_content'])) {
    $GLOBALS['TCA']['tt_content']['types']['dynamic_content'] = [];
}
$GLOBALS['TCA']['tt_content']['types']['dynamic_content'] = array_replace_recursive(
    $GLOBALS['TCA']['tt_content']['types']['dynamic_content'],
    [
        'showitem' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.headers;headers,
                dynamic_content_slot_name;LLL:EXT:mautic/Resources/Private/Language/Backend.xlf:dynamic_content.slot_name,
                bodytext;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext_formlabel,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext'        => true,
                    'richtextConfiguration' => 'default',
                ],
            ],
        ],
    ]
);

/***************
 * Register fields
 */
$GLOBALS['TCA']['tt_content']['columns'] = array_replace_recursive(
    $GLOBALS['TCA']['tt_content']['columns'],
    [
        'dynamic_content_slot_name' => [
            'label'  => 'LLL:EXT:mautic/Resources/Private/Language/Backend.xlf:dynamic_content.slot_name',
            'config' => [
                'type' => 'input',
                'eval' => 'required',
                'size' => 50,
                'max'  => 255,
            ],
        ],
    ]
);
