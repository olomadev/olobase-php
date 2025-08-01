<?php

declare(strict_types=1);

return [
    'authentication' => [
        'adapter' => [
            'type' => \Laminas\Authentication\Adapter\DbTable\CallbackCheckAdapter::class,
            'options' => [
                'table' => 'users',
                'identity_column' => 'email',
                'credential_column' => 'password',
            ],
        ],
        'form' => [
            'username' => 'username',
            'password' => 'password',
        ],
    ],
];
