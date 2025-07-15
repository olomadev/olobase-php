<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_ROOT', dirname(__DIR__));
define('APP_CACHE_PREFIX', 'olobase_app:');

// Delegate static file requests back to the PHP built-in webserver
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

if (! is_file('config/module.config.php')) {
    throw new RuntimeException('Module configuration is missing or incorrect.');
}
/**
 * Self-called anonymous function that creates its own scope and keeps the global namespace clean.
 */
(function () {
    /** @var \Psr\Container\ContainerInterface $container */
    $container = require 'config/container.php';

    /** @var \Mezzio\Application $app */
    $app = $container->get(\Mezzio\Application::class);
    $factory = $container->get(\Mezzio\MiddlewareFactory::class);

    // Execute programmatic/declarative middleware pipeline and routing
    // configuration statements
    (require 'config/pipeline.php')($app, $factory, $container);
    (require 'config/routes.php')($app, $factory, $container);

    // Register module routes ..
    $modules = $container->get('config')['modules'];
    $moduleProviders = [];
    foreach ($modules as $module) {
        $configProviderClass = $module . '\ConfigProvider';
        if (class_exists($configProviderClass) 
            && method_exists($configProviderClass, 'registerRoutes')) {
            $configProviderClass::registerRoutes($container);
        }
    }
    $app->run();
})();
