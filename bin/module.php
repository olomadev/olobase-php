<?php

declare(strict_types=1);

// Command line options
$options = getopt('', ['install:', 'remove:']);

$modulesConfigFile = __DIR__ . '/../config/module.config.php';
$composerJsonFile = __DIR__ . '/../composer.json';
$composerLockFile = __DIR__ . '/../composer.lock';

// Read the existing modules
$modulesConfig = file_exists($modulesConfigFile) ? include $modulesConfigFile : [];

// Read the composer.json
$composerJson = json_decode(file_get_contents($composerJsonFile), true);
$repositories = $composerJson['repositories'] ?? [];
$require = $composerJson['require'] ?? [];

// Function to print the composer install output in green using passthru
function runComposerInstall()
{
    echo "\033[32mRunning composer install...\033[0m\n";
    passthru("composer install", $returnVar);
    if ($returnVar === 0) {
        echo "\033[32mComposer install completed successfully.\033[0m\n";
    } else {
        echo "\033[31mComposer install failed with status code $returnVar.\033[0m\n";
    }
}

// Function to get the module's full composer name
function getModuleNameFromComposerJson(string $modulePath): ?string
{
    $composerFile = $modulePath . '/composer.json';
    if (file_exists($composerFile)) {
        $composerData = json_decode(file_get_contents($composerFile), true);
        return $composerData['name'] ?? null;
    }
    return null;
}

// INSTALL MODULE
if (isset($options['install'])) {
    $moduleName = $options['install'];
    if (!in_array($moduleName, $modulesConfig)) {
        $modulesConfig[] = $moduleName;
        echo "\033[32mInstalling module: $moduleName\n\033[0m";

        $modulePath = "./src/$moduleName";
        $moduleFullName = getModuleNameFromComposerJson(__DIR__ . "/../$modulePath");

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

        runComposerInstall();
    } else {
        echo "\033[33mModule '{$moduleName}' is already enabled in module.config.php.\n\033[0m";
    }
}

// REMOVE MODULE
if (isset($options['remove'])) {
    $moduleName = $options['remove'];
    if (($key = array_search($moduleName, $modulesConfig)) !== false) {
        unset($modulesConfig[$key]);
        echo "\033[32mRemoving module: $moduleName\n\033[0m";

        $modulePath = "./src/$moduleName";
        $moduleFullName = getModuleNameFromComposerJson(__DIR__ . "/../$modulePath");

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

        runComposerInstall();
    } else {
        echo "\033[33mModule '{$moduleName}' is not found in module.config.php.\n\033[0m";
    }
}
