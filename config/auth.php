<?php

return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        'api' => [
            'driver' => 'GuardToken',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'auth-provider',
            'model' => \lumilock\lumilockToolsPackage\App\Models\User::class
        ]
    ]
];