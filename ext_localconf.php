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

// Add the tracking script
if (TYPO3_MODE === 'FE') {
    if ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mautic_typo3']['tracking']) {
        $renderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        $mauticUrl = rtrim($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mautic_typo3']['mauticUrl'], '/') . '/';
        $renderer->addJsInlineCode('Mautic', 
            "(function(w,d,t,u,n,a,m){w['MauticTrackingObject']=n;
            w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
            m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
            })(window,document,'script','" . $mauticUrl . "mtc.js','mt');

            mt('send', 'pageview');");
    }
}
