#!/usr/bin/env php
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('APP_ROOT', dirname(__DIR__));

chdir(__DIR__ . '/../');

require 'vendor/autoload.php';

use Symfony\Component\Console\Application;
use Olobase\ModuleManager\Command\ModuleInstallCommand;
use Olobase\ModuleManager\Command\ModuleRemoveCommand;
use Olobase\ModuleManager\Command\MigrationsListCommand;
use Olobase\ModuleManager\Command\MigrationsMigrateCommand;

$container = require APP_ROOT . '/config/container.php';
$application = new Application();

$config = $container->get('config');

$installCommand = new ModuleInstallCommand('module:install');
$installCommand->setConfig($config);

$removeCommand = new ModuleRemoveCommand('module:remove');
$removeCommand->setConfig($config);

$application->add($installCommand);
$application->add($removeCommand);
$application->add(new MigrationsListCommand('migrations:list'));
$application->add(new MigrationsMigrateCommand('migrations:migrate'));
$application->run();
