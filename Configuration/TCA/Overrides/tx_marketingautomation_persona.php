<?php

declare(strict_types=1);
defined('TYPO3_MODE') || die();

$tempColumns = [
    'tx_marketingautomation_segments' => [
        'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_tca.xlf:tx_marketingautomation_persona.segments',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_marketingautomation_segment',
            'foreign_table_where' => 'ORDER BY title',
            'MM' => 'tx_marketingautomation_segment_mm',
            'MM_opposite_field' => 'items',
            'MM_match_fields' => [
                'tablenames' => 'tx_marketingautomation_persona',
                'fieldname' => 'tx_marketingautomation_segments',
            ],
            'size' => 10,
            'autoSizeMax' => 30,
            'fieldControl' => [
                'updateSegmentsControl' => [
                    'renderType' => 'updateSegmentsControl',
                ],
                'editPopup' => [
                    'disabled' => true,
                ],
                'addRecord' => [
                    'disabled' => true,
                ],
                'listModule' => [
                    'disabled' => true,
                ],
            ],
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tx_marketingautomation_persona', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tx_marketingautomation_persona',
    '--div--;LLL:EXT:mautic/Resources/Private/Language/locallang_tca.xlf:mautic,tx_marketingautomation_segments',
    '',
    'before:--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended'
);
