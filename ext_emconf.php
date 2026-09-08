<?php

$EM_CONF['mautic'] = [
    'title' => 'Marketing Automation - Mautic Adapter',
    'description' => 'Add-on TYPO3 extension that enhances the "marketing-automation" TYPO3 extension by connecting it to the Mautic Marketing Automation platform: Determine "Persona" from Mautic segments. Requires access to a Mautic instance in version 4.4 or later (5.2 or later recommended).',
    'category' => 'fe',
    'state' => 'stable',
    'author_company' => 'Leuchtfeuer Digital Marketing',
    'author_email' => 'dev@leuchtfeuer.com',
    'version' => '13.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'marketing_automation' => '13.0.0-13.9.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'static_info_tables' => '6.7.0',
            'fluid_styled_content' => '13.4.0-13.4.99',
            'form' => '13.4.0',
        ],
    ],
];
