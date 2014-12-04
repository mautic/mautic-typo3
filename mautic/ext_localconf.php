<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	$_EXTKEY,
	'Pi1',
	array(
		'Tracker' => 'index',
	),
	// non-cacheable actions
	array(
        'Tracker' => 'index',
	)
);
