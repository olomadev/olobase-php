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

$argv = $_SERVER['argv'];
$container = require ROOT.'/config/container.php';
$envVariables = $container->get('config')['env_variables'];

// validate command is empty
if (empty($argv[1])) {
    echo "\033[31mCommand not found\033[0m\n";
    die;
}
// check command list
if (false == in_array(trim($argv[1]), ['install','remove','migrations:migrate','migrations:migrate-all','migrations:list'])) {
    echo "\033[31mCommand $argv[1] not found\033[0m\n";
    die;
}
// Command
$command = trim($argv[1]);

// Command line options
$parsed = parseArgv($_SERVER['argv'], ['env', 'module', 'prev', 'to', 'strict']);
$options = $parsed['options'];

if (! isset($options['env'])) {
    echo "\033[31mPlease set an env variable: --env=local\033[0m\n";
    die;
} else if (false == in_array($options['env'], $envVariables)) {
    echo "\033[31mThe env variable you entered does not exist in your 'env_variables' configuration\033[0m\n";
    die;
}
if (! isset($options['module'])) {
    echo "\033[31mPlease set a module variable: --module=moduleName\033[0m\n";
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
if ($command == "install") {
    $moduleName = $options['module'];

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

        runAllModuleMigrations($moduleName, $conn);

    } else {
        echo "\033[33mModule '{$moduleName}' is already enabled in module.config.php.\n\033[0m";
    }
}

// Remove Module
if ($command == "remove") {
    $moduleName = $options['module'];
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
//
// Handle migration commands 
// 
if (in_array($command, ['migrations:migrate', 'migrations:list', 'migrations:migrate-all'])) {

    $laminasDbConfig = $container->get('config')['db'];
    $doctrineDbConfig = convertLaminasDbToDoctrine($laminasDbConfig);
    $conn = DriverManager::getConnection($doctrineDbConfig); // Create Doctrine DBAL connection from container config

    if ($command == "migrations:list") {
        $moduleName = $options['module'];
        passthru("php bin/migrations.php --module={$moduleName} migrations:list --no-interaction --ansi");
    }

    if ($command == "migrations:migrate") {
        $moduleName = $options['module'];
        $prev = $options['prev'] ?? null;
        $toVersion = $options['to'] ?? null;
        $strict = empty($options['strict']) ? false : (bool)$options['strict'];

        runModuleMigrations($moduleName, $conn, $prev, $toVersion, $strict);
    }

    if ($command == "migrations:migrate-all") {
        // $app = new Application();
        // $dirs = glob(ROOT . '/src/*', GLOB_ONLYDIR);
        // $i = 0;
        // foreach ($dirs as $dir) {
        //     $moduleName = basename($dir);
        //     echo "\n\033[34m== $moduleName Migration ==\033[0m\n";

        //     $moduleMigrations = ROOT . "/src/$moduleName/src/Migrations";
        //     if (!is_dir($moduleMigrations)) {
        //         echo "\033[33mMigration folder not found: $moduleName\033[0m\n";
        //         continue;
        //     }
        //     $factory = createDependencyFactory($moduleName, $moduleMigrations, $conn);

        //     if ($i == 0) { // create migrations table one time if does not exists ..
        //         $syncCommand = new SyncMetadataCommand($factory);
        //         $syncCommand->setApplication($app);
        //         $input = new ArrayInput([]);
        //         $output = new ConsoleOutput();

        //         $output->writeln("<info>Running metadata sync for: $moduleName</info>");
        //         $syncCommand->run($input, $output);
        //     }
        //     $migrateCommand = new MigrateCommand($factory);
        //     $app->add($migrateCommand);

        //     $input = new ArrayInput(['command' => 'migrate']);
        //     $output = new ConsoleOutput();
        //     $app->run($input, $output);
        //     ++$i;
        // }
        // exit(0);
    }
}

// Function to parse command arguments
function parseArgv(array $argv, array $expected = []): array
{
    $result = [
        'command' => null,
        'options' => [],
    ];
    $result['command'] = $argv[1] ?? null;

    for ($i = 2; $i < count($argv); $i++) {
        $arg = $argv[$i];

        if (str_starts_with($arg, '--')) {
            $arg = substr($arg, 2);

            if (strpos($arg, '=') !== false) {
                [$key, $value] = explode('=', $arg, 2);
            } else {
                $key = $arg;
                $value = true; // sadece flag gibi
            }
            if (empty($expected) || in_array($key, $expected, true)) {
                $result['options'][$key] = $value;
            }
        }
    }

    return $result;
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
    $configFactory = require __DIR__ . '/migration_config.php';

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

function runAllModuleMigrations($moduleName, $conn)
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

function runModuleMigrations(string $moduleName, \Doctrine\DBAL\Connection $conn, $prev = false, ?string $toVersion = null, $strict = false): void
{
    $moduleMigrations = ROOT . "/src/$moduleName/src/Migrations";
    if (!is_dir($moduleMigrations)) {
        echo "\033[31mMigration folder not found for module: $moduleName\033[0m\n";
        echo "\033[31mCurrent folder: $moduleMigrations\033[0m\n";
        exit(1);
    }
    $factory = createDependencyFactory($moduleName, $moduleMigrations, $conn);

    if ($prev) {
        echo "\033[33mRolling back to previous version..\033[0m\n";
        passthru("php bin/migrations.php --module={$moduleName} migrations:migrate prev --no-interaction --ansi", $exitCode);

        if ($exitCode !== 0) {
            echo "\033[31mRollback failed\033[0m\n";
        }
        echo "\033[32m[OK] Rollback completed for module: $moduleName\033[0m\n";
      die;
    }

    if ($toVersion) {  // migrate to specific version ..
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
                    $stepsToRollback--;
                }
            }
        }
        if ($stepsToRollback === 0) {
            echo "\033[32m[OK] Already at or before target version.\033[0m\n";
            exit(0);
        }
        echo "\033[33mRolling back $stepsToRollback step(s) to reach version: $toVersion\033[0m\n";

        for ($i = 0; $i < $stepsToRollback; $i++) {
            echo "\033[33mRolling back step " . ($i + 1) . "\033[0m\n";
            passthru("php bin/migrations.php --module={$moduleName} migrations:migrate prev --no-interaction --ansi", $exitCode);

            if ($exitCode !== 0) {
                echo "\033[31mRollback failed at step " . ($i + 1) . "\033[0m\n";
                break;
            }
        }
        echo "\033[32m[OK] Rollback completed to version: $toVersion\033[0m\n";

    } else { // run migrations ...

        passthru("php bin/migrations.php --module={$moduleName} migrations:migrate --no-interaction --ansi", $exitCode);

        if ($exitCode !== 0) {
            echo "\033[31mMigration failed\033[0m\n";
        }
        echo "\033[32m[OK] Migration completed for module: $moduleName\033[0m\n";
    }

    exit(0);
}

