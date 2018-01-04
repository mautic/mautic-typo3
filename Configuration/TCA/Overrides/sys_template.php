<?php

defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'mautic',
    'Configuration/TypoScript',
    'Mautic'
);
