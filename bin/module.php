<?php

declare(strict_types=1);

use Laminas\Db\Adapter\Adapter;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Version\Version;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('ROOT', dirname(__DIR__));

require 'vendor/autoload.php';

$container = require ROOT.'/config/container.php';
$envVariables = $container->get('config')['env_variables'];

// Command line options
$options = getopt('', ['install:', 'remove:', 'env:', 'migrate:', 'migrate-all:', 'rollback:', 'steps:', 'to:', 'strict:', 'status:']);

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


// var_dump($options);
// die;

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

// Handle migration commands 
if (isset($options['migrate']) || isset($options['status']) || isset($options['rollback']) || isset($options['migrate-all'])) {

    $laminasDbConfig = $container->get('config')['db'];
    $doctrineDbConfig = convertLaminasDbToDoctrine($laminasDbConfig);
    $conn = DriverManager::getConnection($doctrineDbConfig); // Create Doctrine DBAL connection from container config

    if (isset($options['migrate'])) {
        $moduleName = $options['migrate'];
        runModuleMigrations($moduleName, $conn);
    }

    if (isset($options['status'])) {
        $moduleName = $options['status'];
        statusModuleMigrations($moduleName, $conn);
    }

    if (isset($options['rollback'])) {
        $moduleName = $options['rollback'];
        $steps = isset($options['steps']) ? (int)$options['steps'] : 1;
        $toVersion = $options['to'] ?? null;
        $strict = empty($options['strict']) ? false : true;
        rollbackModuleMigrations($moduleName, $conn, $steps, $toVersion, $strict);
    }

    if (isset($options['migrate-all'])) {
        $app = new Application();
        $dirs = glob(ROOT . '/src/*', GLOB_ONLYDIR);
        $i = 0;
        foreach ($dirs as $dir) {
            $moduleName = basename($dir);
            echo "\n\033[34m== $moduleName Migration ==\033[0m\n";

            $moduleMigrations = ROOT . "/src/$moduleName/src/Migrations";
            if (!is_dir($moduleMigrations)) {
                echo "\033[33mMigration folder not found: $moduleName\033[0m\n";
                continue;
            }
            $factory = createDependencyFactory($moduleName, $moduleMigrations, $conn);

            if ($i == 0) { // create migrations table one time if does not exists ..
                $syncCommand = new SyncMetadataCommand($factory);
                $syncCommand->setApplication($app);
                $input = new ArrayInput([]);
                $output = new ConsoleOutput();

                $output->writeln("<info>Running metadata sync for: $moduleName</info>");
                $syncCommand->run($input, $output);
            }
            $migrateCommand = new MigrateCommand($factory);
            $app->add($migrateCommand);

            $input = new ArrayInput(['command' => 'migrate']);
            $output = new ConsoleOutput();
            $app->run($input, $output);
            ++$i;
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

// Create DependencyFactory for Doctrine Migrations
function createDependencyFactory(string $moduleName, string $migrationsPath, \Doctrine\DBAL\Connection $conn): DependencyFactory
{
    $configFactory = require __DIR__ . '/migrations.php';

    /** @var ConfigurationArray $config */
    $config = $configFactory($moduleName);

    return DependencyFactory::fromConnection($config, new ExistingConnection($conn));
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

function statusModuleMigrations(string $moduleName, \Doctrine\DBAL\Connection $conn): void
{
    $colors = [
        'reset'       => "\033[0m",
        'fg_white'    => "\033[97m",
        'fg_black'    => "\033[30m",
        'bg_green'    => "\033[42m",
        'bg_yellow'   => "\033[43m",
        'fg_red'      => "\033[31m",
        'fg_cyan'     => "\033[36m",
    ];
    echo $colors['fg_white'] . "Migration Status for module: $moduleName\n" . $colors['reset'];
    echo "--------------------------------------------------\n";

    $migrationsPath = ROOT . "/src/$moduleName/src/Migrations";
    if (!is_dir($migrationsPath)) {
        echo $colors['fg_red'] . "Migration path not found: $migrationsPath\n" . $colors['reset'];
        return;
    }
    $factory = createDependencyFactory($moduleName, $migrationsPath, $conn);

    /** @var ExecutedMigrationsList $executedMigrations */
    $executedMigrations = $factory->getMetadataStorage()->getExecutedMigrations();

    /** @var AvailableMigrationsList $availableMigrations */
    $availableMigrations = $factory->getMigrationRepository()->getMigrations();

    if (count($availableMigrations->getItems()) === 0) {
        $line = "No available migrations found.";
        $line = str_pad($line, 50, ' ');
        echo $colors['bg_yellow'] . $colors['fg_black'] . $line . $colors['reset'] . "\n";
        return;
    }
    foreach ($availableMigrations->getItems() as $migration) {
        $version = $migration->getVersion();

        if ($executedMigrations->hasMigration($version)) {
            $line = "  [APPLIED] $version";
            $line = str_pad($line, 50, ' ');
            echo $colors['bg_green'] . $colors['fg_black'] . $line . $colors['reset'] . "\n";
        } else {
            $line = "  [PENDING] $version";
            $line = str_pad($line, 50, ' ');
            echo $colors['bg_yellow'] . $colors['fg_black'] . $line . $colors['reset'] . "\n";
        }
    }

    echo "--------------------------------------------------\n";
}

function rollbackModuleMigrations(string $moduleName, \Doctrine\DBAL\Connection $conn, int $steps = 1, ?string $toVersion = null, $strict = false): void
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
    $output = new ConsoleOutput();

    if ($toVersion) {
        echo "\033[33m<<--- Rolling back to version: $toVersion\033[0m\n";

        $executed = $factory->getMetadataStorage()->getExecutedMigrations();
        $executedMigrations = (array)$executed->getItems();

        $executedVersions = [];
        foreach ($executedMigrations as $migrationItem)  {
            $version = $migrationItem->getVersion(); // Doctrine\Migrations\Version\Version
            $versionString = (string) $version; // e.g.: "Modules\Migrations\Version20250707151000"
            $executedVersions[] = $versionString;
        }
        $namespace = $moduleName . '\\Migrations';
        if (!str_starts_with($toVersion, $namespace)) {
            $fullToVersion = $namespace . '\\' . $toVersion;
        } else {
            $fullToVersion = $toVersion;
        }
        $toVersionObj = new Version($fullToVersion);
        $executedVersions = array_reverse($executedVersions); // Ters sıraya çevir (en yeni en başta)


        $stepsToRollback = 0;
        foreach ($executedVersions as $version) {
            $stepsToRollback++;
            if ($version === (string)$toVersionObj) {
                if (!$strict) {
                    $stepsToRollback--; // dahil etmeyeceğiz
                }
                break;
            }
        }
        // var_dump($strict);
        // echo $stepsToRollback."\n";
        // die;

        if ($stepsToRollback === 0) {
            echo "\033[32m[OK] Already at or before target version.\033[0m\n";
            exit(0);
        }

        echo "\033[33mRolling back $stepsToRollback step(s) to reach version: $toVersion\033[0m\n";

        for ($i = 0; $i < $stepsToRollback; $i++) {
            echo "\033[33m<<--- Rolling back step " . ($i + 1) . "\033[0m\n";

            $input = new ArrayInput([
                'command' => 'migrate',
                'version' => 'prev',
                '--no-interaction' => true,
            ]);
            $exitCode = $app->run($input, $output);

            if ($exitCode !== 0) {
                echo "\033[31mRollback failed at step " . ($i + 1) . "\033[0m\n";
                break;
            }
        }

        echo "\033[32m[OK] Rollback completed to version: $toVersion\033[0m\n";

    } else {
        for ($i = 0; $i < $steps; $i++) {
            echo "\033[33m<<--- Rolling back step " . ($i + 1) . "\033[0m\n";

            $input = new ArrayInput([
                'command' => 'migrate',
                'version' => 'prev',
                '--no-interaction' => true,
            ]);
            $exitCode = $app->run($input, $output);

            if ($exitCode !== 0) {
                echo "\033[31mRollback failed at step " . ($i + 1) . "\033[0m\n";
                break;
            }
        }

        echo "\033[32m[OK] Rollback completed ($steps step(s)) for module: $moduleName\033[0m\n";
    }

    exit(0);
}
