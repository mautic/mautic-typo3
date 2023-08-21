<?php

declare(strict_types=1);
defined('TYPO3_MODE') || die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_tca.xlf:tx_mautic_domain_model_tag',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'searchFields' => 'title',
        'typeicon_classes' => [
            'default' => 'mimetypes-x-tx_marketingautomation_persona',
        ],
        'hideTable' => true,
        'rootLevel' => 1,
        'security' => [
            'ignoreRootLevelRestriction' => true,
        ],
    ],
    'interface' => [],
    'types' => [
        '1' => [
            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,'
                . 'title,'
                . '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
            ',
        ],
    ],
    'palettes' => [],
    'columns' => [
        'title' => [
            'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_tca.xlf:tx_mautic_domain_model_tag.title',
            'config' => [
                'type' => 'input',
                'width' => 200,
                'eval' => 'trim,required',
            ],
        ],
        'items' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];
