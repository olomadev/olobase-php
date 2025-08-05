<?php

declare(strict_types=1);

$targetDir = dirname(__DIR__, 4). '/config/autoload';

$targetFile = $targetDir . '/authentication.global.php';

if (file_exists($targetFile)) {
    echo "\033[33m⚠ File 'authentication.global.php' already exists at config/autoload/. Skipping ...\n\033[0m";
    return;
}

$template = <<<PHP
<?php

return [
    'authentication' => [
        'adapter' => [
            'type' => \Laminas\Authentication\Adapter\DbTable\CallbackCheckAdapter::class,
            'class' => 'AuthenticationAdapter',
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
        'excluded_fields' => [
            'password',
        ]
    ],
];
PHP;

file_put_contents($targetFile, $template);
echo "\033[32m✔ Configuration file 'auth-config.global.php' successfully created in config/autoload/\n\033[0m";
