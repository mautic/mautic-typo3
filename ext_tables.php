<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE === 'BE') {

    /*
     * Registers a Backend Module
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Mautic.'.$_EXTKEY,
        'tools',
        'mauticauth',
        '',                        // Position
        [
            'Authorisation' => 'list, authFacebook, authoriseLinkedIn',

        ],
        [
            'access' => 'user,group',
            'icon'   => 'EXT:'.$_EXTKEY.'/Resources/Public/Icons/Extension.png',
            'labels' => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/Backend.xlf:tx_mautic_domain_model_mautic',
        ]
    );
}
