<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_db.xlf:tx_typo3idp_domain_model_mauticoauthtoken',
        'label' => 'access_token',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'rootLevel' => 1,
        'hideTable' => true,
        'searchFields' => 'access_token',
        'iconfile' => 'EXT:typo3_idp/Resources/Public/Icons/mautic.png'
    ],
    'interface' => [
        'showRecordFieldList' => 'access_token, refresh_token, expires',
    ],
    'types' => [
        '1' => ['showitem' => 'access_token, refresh_token, expires'],
    ],
    'columns' => [
        'expires' => [
            'exclude' => false,
            'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_db.xlf:x_typo3idp_domain_model_mauticoauthtoken.expires',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 10,
                'eval' => 'datetime',
                'default' => time()
            ],
        ],
        'access_token' => [
            'exclude' => false,
            'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_db.xlf:x_typo3idp_domain_model_mauticoauthtoken.accesstoken',
            'config' => [
                'type' => 'input',
                'size' => 32,
                'max' => 255,
                'eval' => 'trim,required',
            ]
        ],
        'refresh_token' => [
            'exclude' => false,
            'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_db.xlf:x_typo3idp_domain_model_mauticoauthtoken.refreshtoken',
            'config' => [
                'type' => 'input',
                'size' => 32,
                'max' => 255,
                'eval' => 'trim,required',
            ]
        ],
    ],
];
