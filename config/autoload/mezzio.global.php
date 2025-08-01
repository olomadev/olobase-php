<?php

declare(strict_types=1);

return [
    //
    // Installed modules
    //
    'modules' => require __DIR__ . '/../module.config.php',
    //
    // Available env variables
    //
    'env_variables' => [
        'local',
        'prod',
        'test'
    ],
];
