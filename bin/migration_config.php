<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;

return static function (string $moduleName): ConfigurationArray {
    $migrationDir = ROOT . '/src/' . $moduleName . '/src/Migrations';
    $namespace = $moduleName . '\\Migrations';

    return new ConfigurationArray([
        'migrations_paths' => [
            $namespace => $migrationDir,
        ],
        'table_storage' => [
            'table_name' => 'migrations_' . strtolower($moduleName),
        ],
        'all_or_nothing' => true,
        'check_database_platform' => true,
    ]);
};