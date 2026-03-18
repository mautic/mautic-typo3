<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
defined('TYPO3') || die();

$temporaryColumns = [
    'tx_mautic_tags' => [
        'exclude' => true,
        'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_tca.xlf:pages.tx_mautic_tags',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_mautic_domain_model_tag',
            'foreign_table_where' => 'ORDER BY title',
            'MM' => 'tx_mautic_page_tag_mm',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 999,
            'multiple' => 0,
            'fieldControl' => [
                'updateTagsControl' => [
                    'renderType' => 'updateTagsControl',
                    'title' => 'Synchronize Tags',
                ],
                'addRecord' => [
                    'disabled' => false,
                    'pid' => 0,
                    'table' => 'tx_mautic_domain_model_tag',
                    'title' => 'Create new Tag',
                ],
            ],
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('pages', $temporaryColumns);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;LLL:EXT:mautic/Resources/Private/Language/locallang_tca.xlf:mautic,tx_mautic_tags'
);
