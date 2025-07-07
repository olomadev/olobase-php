<?php

declare(strict_types=1);

use Laminas\Db\Adapter\Adapter;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollbackCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('ROOT', dirname(__DIR__));

require 'vendor/autoload.php';

$container = require ROOT.'/config/container.php';
$envVariables = $container->get('config')['env_variables'];

// Command line options
$options = getopt('', ['install:', 'remove:', 'env:', 'migrate:', 'migrate-all:', 'rollback:']);

if (! isset($options['env'])) {
    echo "\033[31mPlease set an env variable: --env=local\033[0m\n";
    die;
} else if (false == in_array($options['env'], $envVariables)) {
    echo "\033[31mThe env variable you entered does not exist in your 'env_variables' configuration\033[0m\n";
    die;
}
putenv('APP_ENV='.trim($options['env']));

// Laminas db adapter
$adapter = $container->get(Adapter::class);

$modulesConfigFile = ROOT . '/config/module.config.php';
$composerJsonFile = ROOT . '/composer.json';
$composerLockFile = ROOT . '/composer.lock';

// Read the existing modules
$modulesConfig = file_exists($modulesConfigFile) ? include $modulesConfigFile : [];

// Read the composer.json
$composerJson = json_decode(file_get_contents($composerJsonFile), true);
$repositories = $composerJson['repositories'] ?? [];
$require = $composerJson['require'] ?? [];


// Install Module
if (isset($options['install'])) {
    $moduleName = $options['install'];

    if (!in_array($moduleName, $modulesConfig)) {
        $modulesConfig[] = $moduleName;
        echo "\033[32mInstalling module: $moduleName\n\033[0m";

        $modulePath = "./src/$moduleName";
        $moduleFullPath = ROOT . "/$modulePath";
        $moduleFullName = getModuleNameFromComposerJson($moduleFullPath);

        // Add to repositories
        $repositories[] = [
            'type' => 'path',
            'url' => $modulePath,
            'options' => ['symlink' => true],
        ];

        // Add to require
        if ($moduleFullName) {
            $require[$moduleFullName] = '*';
        }

        // Save updated composer.json
        $composerJson['repositories'] = $repositories;
        $composerJson['require'] = $require;
        file_put_contents($composerJsonFile, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Save updated module config
        file_put_contents($modulesConfigFile, "<?php\nreturn [\n    " . implode(",\n    ", array_map(fn($m) => "'$m'", $modulesConfig)) . "\n];\n");

        // Mezzio register
        exec("composer mezzio mezzio:module:register $moduleName");

        echo "\033[32mAutoload updated successfully.\n\033[0m";

        if (file_exists($composerLockFile)) {
            unlink($composerLockFile);
            echo "\033[32mcomposer.lock file removed successfully.\n\033[0m";
        }

        $return = runComposerInstall();
        if (false == $return) {
            echo "\033[31mAborting: Migration skipped due to composer install failure.\033[0m\n";
            exit(1); // or return
        }
        // Running migrations ...
        echo "\033[32mRunning migrations for module: $moduleName\n\033[0m";

        $laminasDbConfig = $container->get('config')['db'];
        $doctrineDbConfig = convertLaminasDbToDoctrine($laminasDbConfig);
        $conn = \Doctrine\DBAL\DriverManager::getConnection($doctrineDbConfig);

        runModuleMigrations($moduleName, $conn);

    } else {
        echo "\033[33mModule '{$moduleName}' is already enabled in module.config.php.\n\033[0m";
    }
}

// Remove Module
if (isset($options['remove'])) {
    $moduleName = $options['remove'];
    if (($key = array_search($moduleName, $modulesConfig)) !== false) {
        unset($modulesConfig[$key]);
        echo "\033[32mRemoving module: $moduleName\n\033[0m";

        $modulePath = "./src/$moduleName";
        $moduleFullPath = ROOT . "/$modulePath";
        $moduleFullName = getModuleNameFromComposerJson($moduleFullPath);

        // Remove from repositories
        $repositories = array_filter($repositories, fn($repo) => !isset($repo['url']) || strpos($repo['url'], $moduleName) === false);
        $composerJson['repositories'] = array_values($repositories);

        // Remove from require
        if ($moduleFullName && isset($require[$moduleFullName])) {
            unset($require[$moduleFullName]);
            $composerJson['require'] = $require;

            // Run composer remove
            exec("composer remove $moduleFullName", $output, $returnVar);
            if ($returnVar === 0) {
                echo "\033[32mModule '{$moduleFullName}' removed successfully from composer.json.\n\033[0m";
            } else {
                echo "\033[31mFailed to remove module '{$moduleFullName}' from composer.json.\n\033[0m";
            }
        }

        // Save updated composer.json
        file_put_contents($composerJsonFile, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Save updated module config
        file_put_contents($modulesConfigFile, "<?php\nreturn [\n    " . implode(",\n    ", array_map(fn($m) => "'$m'", $modulesConfig)) . "\n];\n");

        // Mezzio deregister
        exec("composer mezzio mezzio:module:deregister $moduleName");

        echo "\033[32mAutoload updated successfully.\n\033[0m";

        if (file_exists($composerLockFile)) {
            unlink($composerLockFile);
            echo "\033[32mcomposer.lock file removed successfully.\n\033[0m";
        }

        $return = runComposerInstall();
        if (false == $return) {
            echo "\033[31mAborting: Rollback migration skipped due to composer remove failure.\033[0m\n";
            exit(1); // or return
        }
        // Running rollback migrations ...
        echo "\033[32mRunning rollback migrations for module: $moduleName\n\033[0m";

        $laminasDbConfig = $container->get('config')['db'];
        $doctrineDbConfig = convertLaminasDbToDoctrine($laminasDbConfig);
        $conn = \Doctrine\DBAL\DriverManager::getConnection($doctrineDbConfig);

        rollbackModuleMigrations($moduleName, $conn);

    } else {
        echo "\033[33mModule '{$moduleName}' is not found in module.config.php.\n\033[0m";
    }
}

// --- Handle migration commands ---
if (isset($options['migrate']) || isset($options['rollback']) || isset($options['migrate-all'])) {

    $laminasDbConfig = $container->get('config')['db'];
    $doctrineDbConfig = convertLaminasDbToDoctrine($laminasDbConfig);
    $conn = DriverManager::getConnection($doctrineDbConfig); // Create Doctrine DBAL connection from container config

    if (isset($options['migrate'])) {
        $moduleName = $options['migrate'];
        runModuleMigrations($moduleName, $conn);
    }

    if (isset($options['rollback'])) {
        $moduleName = $options['rollback'];
        rollbackModuleMigrations($moduleName, $conn);
    }

    if (isset($options['migrate-all'])) {
        $app = new Application();
        $modulesDir = ROOT . '/src';
        $dirs = glob($modulesDir . '/*', GLOB_ONLYDIR);

        foreach ($dirs as $dir) {
            $moduleName = basename($dir);
            echo "\n\033[34m== $moduleName Migration ==\033[0m\n";

            $moduleMigrations = $dir . '/src/Migrations';
            if (!is_dir($moduleMigrations)) {
                echo "\033[33mMigration folder not found: $moduleName\033[0m\n";
                continue;
            }
            $factory = createDependencyFactory($moduleName, $moduleMigrations, $conn);

            $migrateCommand = new MigrateCommand($factory);
            $app->add($migrateCommand);

            $input = new ArrayInput(['command' => 'migrate']);
            $output = new ConsoleOutput();
            $app->run($input, $output);
        }
        exit(0);
    }
}

// Function to print the composer install output in green using passthru
function runComposerInstall(): bool
{
    echo "\033[32mRunning composer install...\033[0m\n";
    passthru("composer install", $returnVar);
    if ($returnVar === 0) {
        echo "\033[32mComposer install completed successfully.\033[0m\n";
        return true;
    } else {
        echo "\033[31mComposer install failed with status code $returnVar.\033[0m\n";
        return false;
    }
}

// Function to get the module's full composer name
function getModuleNameFromComposerJson(string $moduleFullPath): ?string
{
    $composerFile = $moduleFullPath . '/composer.json';
    if (file_exists($composerFile)) {
        $composerData = json_decode(file_get_contents($composerFile), true);
        return $composerData['name'] ?? null;
    }
    return null;
}

// --- Create DependencyFactory for Doctrine Migrations ---
function createDependencyFactory(string $moduleName, string $migrationsPath, \Doctrine\DBAL\Connection $conn): DependencyFactory
{
    $configFactory = require __DIR__ . '/migrations.php';

    /** @var ConfigurationArray $config */
    $config = $configFactory($moduleName);

    return DependencyFactory::fromConnection($config, new ExistingConnection($conn));
}

// --- Convert laminas db configuration to doctrine ---
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

function runModuleMigrations($moduleName, $conn)
{
    $app = new Application();
    $moduleMigrations = ROOT . "/src/$moduleName/src/Migrations";

    if (!is_dir($moduleMigrations)) {
        echo "\033[31mMigration folder not found for module: $moduleName\033[0m\n";
        echo "\033[31mCurrent folder: $moduleMigrations\033[0m\n";
        exit(1);
    }
    $factory = createDependencyFactory($moduleName, $moduleMigrations, $conn);

    $syncCommand = new SyncMetadataCommand($factory);
    $syncCommand->setApplication($app);
    $input = new ArrayInput([]);
    $output = new ConsoleOutput();

    $output->writeln("<info>Running metadata sync for: $moduleName</info>");
    $syncCommand->run($input, $output);
    
    $migrateCommand = new MigrateCommand($factory);
    $app->add($migrateCommand);

    $input = new ArrayInput(['command' => 'migrate']);
    $output = new ConsoleOutput();
    $app->run($input, $output);
    exit(0);
}

function rollbackModuleMigrations($moduleName, $conn)
{
    $app = new Application();
    $moduleMigrations = ROOT . "/src/$moduleName/src/Migrations";

    if (!is_dir($moduleMigrations)) {
        echo "\033[31mMigration folder not found for module: $moduleName\033[0m\n";
        echo "\033[31mCurrent folder: $moduleMigrations\033[0m\n";
        exit(1);
    }
    $factory = createDependencyFactory($moduleName, $moduleMigrations, $conn);

    $app->add(new MigrateCommand($factory));

    // Roll back to the previous version
    $input = new ArrayInput([
        'command' => 'migrate',
        'version' => 'prev',
        '--no-interaction' => true,
    ]);
    $output = new ConsoleOutput();
    $app->run($input, $output);
    exit(0);
}