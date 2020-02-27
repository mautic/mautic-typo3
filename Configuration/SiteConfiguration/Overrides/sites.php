<?php
// Experimental example to add a new field to the site configuration

// Configure a new simple required input field to site
$mauticSiteConfigurationColumns = [
    'tx_mautic_authorize' => [
        'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:authorization.authorize',
        'config' => [
            'type' => 'user',
            'userFunc' => \Bitmotion\Mautic\Service\MauticAuthorizeService::class . '->getAuthorizeButton',
        ]
    ],
    'tx_mautic_baseUrl' => [
        'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:authorization.baseUrl',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
        ],
    ],
    'tx_mautic_publicKey' => [
        'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:authorization.publicKey',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
        ],
    ],
    'tx_mautic_secretKey' => [
        'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:authorization.secretKey',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
        ],
    ],
    'tx_mautic_accessToken' => [
        'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:authorization.accessToken',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
            'readOnly' => true
        ],
    ],
    'tx_mautic_accessTokenSecret' => [
        'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:authorization.accessTokenSecret',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
            'readOnly' => true
        ],
    ],
    'tx_mautic_tracking' => [
        'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:tracking.tracking',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                ],
            ],
            'default' => 0,
        ],
    ],
    'tx_mautic_trackingScriptOverride' => [
        'label' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:tracking.trackingScriptOverride',
        'config' => [
            'type' => 'text',
            'renderType' => 't3editor',
            'format' => 'javascript',
            'rows' => 5,
        ],
    ],
];

$mauticSiteConfigurationPalettes = [
    'tx-mautic-authorization' => [
        'label' => 'Mautic Authorization',
        'showitem' => 'tx_mautic_baseUrl, tx_mautic_authorize, --linebreak--, tx_mautic_publicKey, tx_mautic_secretKey, --linebreak--, tx_mautic_accessToken, tx_mautic_accessTokenSecret',
    ],
    'tx-mautic-tracking' => [
        'label' => 'Mautic Tracking',
        'showitem' => 'tx_mautic_tracking, --linebreak--, tx_mautic_trackingScriptOverride',
    ],
];

//$GLOBALS['SiteConfiguration']['site_language']['columns'] += $mauticSiteConfigurationColumns;
//$GLOBALS['SiteConfiguration']['site_language']['palettes'] += $mauticSiteConfigurationPalettes;
//$GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem'] .= ', --palette--;;tx-mautic-authorization, --palette--;;tx-mautic-tracking';
