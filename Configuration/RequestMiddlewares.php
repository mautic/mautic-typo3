<?php

declare(strict_types=1);

return [
    'frontend' => [
        'bitmotion/mautic/authorize' => [
            'target' => \Bitmotion\Mautic\Middleware\AuthorizeMiddleware::class,
            'after' => [
                'typo3/cms-frontend/backend-user-authentication',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
            ],
        ],
    ],
];
