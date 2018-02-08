<?php


return [

// OAuth token catcher
    'MauticOAuth' => [
        'path' => '/mauticoauth',
        'target' => Mautic\Mautic\Controller\AuthorisationController::class . '::saveTokensAction'
    ],
];