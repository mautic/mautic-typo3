<?php

defined('TYPO3_MODE') || die();

/***************
 * Add content element group to selector list
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/Backend.xlf:content_group.mautic',
        '--div--',
    ],
    '--div--',
    'before'
);
