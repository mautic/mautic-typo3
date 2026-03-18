<?php

use Leuchtfeuer\Mautic\Controller\BackendController;
return [
    'tools_Api' => [
        'parent' => 'tools',
        'access' => 'admin',
        'iconIdentifier' => 'tx_mautic-mautic-icon',
        'labels' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'Mautic',
        'controllerActions' => [
            BackendController::class => [
                'show',
                'save',
            ],
        ],
    ],
];
