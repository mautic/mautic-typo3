<?php
defined('TYPO3_MODE') || die;

call_user_func(function () {
    // Assign the hooks for pushing newly created and edited forms to Mautic
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDuplicate'][1489959059] =
        \Bitmotion\Mautic\Hooks\MauticFormHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDelete'][1489959059] =
        \Bitmotion\Mautic\Hooks\MauticFormHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormSave'][1489959059] =
        \Bitmotion\Mautic\Hooks\MauticFormHook::class;

    // Backend Module
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Bitmotion.Mautic',
        'tools',
        'api',
        'bottom',
        [
            'Backend' => 'show, save'
        ],
        [
            'access' => 'admin',
            'icon' => 'EXT:mautic/Resources/Public/Icons/mautic-with-background.png',
            'labels' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf',
        ]
    );
});
