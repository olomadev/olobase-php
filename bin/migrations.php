#!/usr/bin/env php
<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
define('APP_ROOT', dirname(__DIR__));

chdir(__DIR__ . '/../');

require APP_ROOT . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Tools\Console\Command;
use Olobase\Command\DoctrineHelper;

// get module name
$argv = $_SERVER['argv'];
$module = null;

foreach ($argv as $i => $arg) {
    if (str_starts_with($arg, '--module=')) {
        $module = trim(substr($arg, strlen('--module=')));
        unset($_SERVER['argv'][$i]); // Symfony Console’a geçmesin
        break;
    }
}

if (!$module) {
    echo "\033[31m[ERROR]\033[0m Please provide a module name using the --module option.\n";
    exit(1);
}

// migration path
$migrationPath = APP_ROOT . "/src/{$module}/src/Migrations";
if (!is_dir($migrationPath)) {
    echo "\033[31m[ERROR]\033[0m Migration path not found: $migrationPath\n";
    exit(1);
}

// migration config
$config = DoctrineHelper::createMigrationConfig($module);

// db connection
$container = require APP_ROOT . '/config/container.php';
$laminasDbConfig = $container->get('config')['db'];
$doctrineDbConfig = DoctrineHelper::formatLaminasDbConfig($laminasDbConfig);
$conn = DriverManager::getConnection($doctrineDbConfig);

// dependecy factory
$dependencyFactory = DependencyFactory::fromConnection($config, new ExistingConnection($conn));

// symfony console app
$cli = new Application("Doctrine Migrations for module: $module");

$cli->addCommands([
    new Command\DumpSchemaCommand($dependencyFactory),
    new Command\ExecuteCommand($dependencyFactory),
    new Command\GenerateCommand($dependencyFactory),
    new Command\LatestCommand($dependencyFactory),
    new Command\ListCommand($dependencyFactory),
    new Command\MigrateCommand($dependencyFactory),
    new Command\RollupCommand($dependencyFactory),
    new Command\StatusCommand($dependencyFactory),
    new Command\SyncMetadataCommand($dependencyFactory),
    new Command\VersionCommand($dependencyFactory),
]);

try {
    $cli->run();
} catch (\Throwable $e) {
    echo "\033[31m[RUN ERROR]\033[0m " . $e->getMessage() . "\n";
}
