<?php

defined('TYPO3_MODE') or die();

/*
 * This extension was developed by Beech.it
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
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
if (TYPO3_MODE === 'FE') {
    $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mautic_typo3'] ?? '');

    $mauticUrl = $extensionConfiguration['mauticUrl'];
    if (!empty($mauticUrl) && $extensionConfiguration['tracking']) {
        $mauticUrl = rtrim($mauticUrl, '/').'/';

        $renderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);

        $renderer->addJsInlineCode('Mautic', "(function(w,d,t,u,n,a,m){w['MauticTrackingObject']=n;
            w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
            m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
            })(window,document,'script','" .$mauticUrl."mtc.js','mt');
            mt('send', 'pageview');");
    }
}
