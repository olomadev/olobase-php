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

    // User Roles (private)
    $app->route('/api/authorization/userRoles/assign', [...$auth, ...[Authorization\Handler\UserRoles\AssignHandler::class]], ['PUT']);
    $app->route('/api/authorization/userRoles/unassign', [...$auth, ...[Authorization\Handler\UserRoles\UnassignHandler::class]], ['PUT']);
    $app->route('/api/authorization/userRoles/findAllByPaging/:roleId', [...$auth, ...[Authorization\Handler\UserRoles\FindAllByPagingHandler::class]], ['GET']);

    $app->route('/api/authorization/permissions/update/:id', [...$auth, [Authorization\Handler\Permissions\UpdateHandler::class]], ['PUT']);
    $app->route('/api/authorization/permissions/findAll', [JwtAuthenticationMiddleware::class, Authorization\Handler\Permissions\FindAllHandler::class], ['GET']);
    $app->route('/api/authorization/permissions/findAllByPaging', [...$auth, [Authorization\Handler\Permissions\FindAllByPagingHandler::class]], ['GET']);
};
