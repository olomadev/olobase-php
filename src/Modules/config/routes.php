<?php

declare(strict_types=1);

use Mezzio\Application;
use Psr\Container\ContainerInterface;
use Authentication\Middleware\JwtAuthenticationMiddleware;

return function (Application $app, ContainerInterface $container) {

    $auth = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
    ];
    // Modules (private)
    $app->route('/api/modules/create', [...$auth, ...[Modules\Handler\CreateHandler::class]], ['POST']);
    $app->route('/api/modules/update/:moduleId', [...$auth, ...[Modules\Handler\UpdateHandler::class]], ['PUT']);
    $app->route('/api/modules/delete/:moduleId', [...$auth, ...[Modules\Handler\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/modules/findAll', [Modules\Handler\FindAllHandler::class], ['GET']);
    $app->route('/api/modules/findAllByPaging', [...$auth, ...[Modules\Handler\FindAllByPagingHandler::class]], ['GET']);
};
