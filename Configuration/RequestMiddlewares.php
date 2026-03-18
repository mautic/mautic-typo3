<?php

declare(strict_types=1);

use Leuchtfeuer\Mautic\Middleware\AuthorizeMiddleware;

return [
    'frontend' => [
        'Leuchtfeuer/mautic/authorize' => [
            'target' => AuthorizeMiddleware::class,
            'after' => [
                'typo3/cms-frontend/backend-user-authentication',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
            ],
        ],
    ],
];
