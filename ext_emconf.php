<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mautic".
 *
 * Auto generated 20-06-2018 11:55
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Marketing Automation - Mautic Adapter',
    'description' => 'Add-on TYPO3 extension that enhances the "marketing-automation" TYPO3 extension by connecting it to the Mautic Marketing Automation platform: Determine "Persona" from Mautic segments. Also provides additional services e.g. language synchronisation between Mautic and TYPO3.',
    'category' => 'fe',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'author' => 'Bitmotion GmbH',
    'author_company' => 'Bitmotion GmbH',
    'author_email' => 'typo3-ext@bitmotion.de',
    'clearCacheOnLoad' => 0,
    'version' => '3.1.0-dev',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
            'marketing_automation' => '',
        ],
        'conflicts' => [],
        'suggests' => [
            'static_info_tables' => '6.7.0',
            'form' => '9.5.0'
        ],
    ],
];

