<?php
defined('TYPO3_MODE') or die();

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// Assign the hooks for pushing newly created and edited forms to Mautic
if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormCreate'][1489959059]
        = \Mautic\MauticTypo3\Hooks\FormProcessHooks::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDuplicate'][1489959059]
        = \Mautic\MauticTypo3\Hooks\FormProcessHooks::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDelete'][1489959059]
        = \Mautic\MauticTypo3\Hooks\FormProcessHooks::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormSave'][1489959059]
        = \Mautic\MauticTypo3\Hooks\FormProcessHooks::class;
}