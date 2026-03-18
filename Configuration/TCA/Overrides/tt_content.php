<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
defined('TYPO3') || die();

/***************
 * Add Content Element
 */
$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['mautic_form'] = 'tx_mautic-mautic-icon';
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'LLL:EXT:mautic/Resources/Private/Language/locallang_tca.xlf:content_element.mautic_form',
        'mautic_form',
        'tx_mautic-mautic-icon',
    ]
);

/***************
 * Configure element type
 */
if (!is_array($GLOBALS['TCA']['tt_content']['types']['mautic_form'] ?? null)) {
    $GLOBALS['TCA']['tt_content']['types']['mautic_form'] = [];
}
$GLOBALS['TCA']['tt_content']['types']['mautic_form'] = array_replace_recursive(
    $GLOBALS['TCA']['tt_content']['types']['mautic_form'],
    [
        'showitem' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.headers;headers,
                mautic_form_id,
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
    ]
);

/***************
 * Register fields
 */
$GLOBALS['TCA']['tt_content']['columns'] = array_replace_recursive(
    $GLOBALS['TCA']['tt_content']['columns'],
    [
        'mautic_form_id' => [
            'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_tca.xlf:content_element.mautic_form',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:form/Resources/Private/Language/Database.xlf:tt_content.pi_flexform.formframework.selectPersistenceIdentifier',
                        'value' => 0,
                    ],
                ],
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
    ]
);

// Register the plugin
ExtensionUtility::registerPlugin(
    'Mautic',
    'Form',
    'LLL:EXT:mautic/Resources/Private/Language/locallang_tca.xlf:content_element.mautic_form',
    'tx_mautic-mautic-icon'
);
