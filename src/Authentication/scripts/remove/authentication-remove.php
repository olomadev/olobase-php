<?php

declare(strict_types=1);

$targetDir = __DIR__ . '/config/autoload';
$authenticationConfigFile = $targetDir . '/authentication.global.php';

if (file_exists($authenticationConfigFile)) {
    unlink($authenticationConfigFile);
    echo "\033[33m⚠ File 'authentication.global.php' removed successfully from config/autoload/.\n\033[0m";
} else {
    echo "\033[32m✔ No 'authentication.global.php' found in config/autoload/. Skipped remove operation.\n\033[0m";
}