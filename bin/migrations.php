<?php

declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('ROOT', dirname(__DIR__));

require 'vendor/autoload.php';

$container = require ROOT.'/config/container.php';
$laminasDbConfig = $container->get('config')['db'];

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Application;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Tools\Console\Command;

// 1. Argümanlardan --module parametresini yakala ve çıkar
$argv = $_SERVER['argv'];
$moduleName = null;

foreach ($argv as $i => $arg) {
    if (str_starts_with($arg, '--module=')) {
        $moduleName = substr($arg, strlen('--module='));
        unset($_SERVER['argv'][$i]); // Symfony'ye geçmesin
        break;
    }
}

if (!$moduleName) {
    echo "Please provide a module name using --module option.\n";
    exit(1);
}

// 2. Migration path kontrolü
$migrationPath = __DIR__ . "/../src/{$moduleName}/src/Migrations";
if (!is_dir($migrationPath)) {
    echo "\033[31mMigration path not found: $migrationPath\033[0m\n";
    exit(1);
}

// 3. Migration config oluştur
$config = new ConfigurationArray([
    'migrations_paths' => [
        "{$moduleName}\\Migrations" => $migrationPath
    ],
    'table_storage' => [
        'table_name' => 'migrations_' . strtolower($moduleName),
    ],
    'all_or_nothing' => true,
    'check_database_platform' => true,
]);

// 4. DB bağlantısı
$laminasDbConfig = $container->get('config')['db'];
$doctrineDbConfig = convertLaminasDbToDoctrine($laminasDbConfig);
$conn = DriverManager::getConnection($doctrineDbConfig);

// 5. Dependency Factory
$dependencyFactory = DependencyFactory::fromConnection($config, new ExistingConnection($conn));

// 6. Symfony Console App
$cli = new Application("Doctrine Migrations for module: $moduleName");
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
    echo "[RUN ERROR] " . $e->getMessage() . "\n";
}


// Convert laminas db configuration to doctrine
function convertLaminasDbToDoctrine(array $laminasConfig): array
{
    return [
        'driver'        => strtolower($laminasConfig['driver']),
        'host'          => $laminasConfig['hostname'] ?? '127.0.0.1',
        'user'          => $laminasConfig['username'] ?? 'root',
        'password'      => $laminasConfig['password'] ?? '',
        'dbname'        => $laminasConfig['database'] ?? '',
        'charset'       => 'utf8mb4',
        'driverOptions' => $laminasConfig['driver_options'] ?? [],
    ];
}