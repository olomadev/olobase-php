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
    // Roles (private)
    $app->route('/api/authorization/roles/create', [...$auth, ...[Authorization\Handler\Roles\CreateHandler::class]], ['POST']);
    $app->route('/api/authorization/roles/update/:roleId', [...$auth, ...[Authorization\Handler\Roles\UpdateHandler::class]], ['PUT']);
    $app->route('/api/authorization/roles/delete/:roleId', [...$auth, ...[Authorization\Handler\Roles\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/authorization/roles/findAll', [Authorization\Handler\Roles\FindAllHandler::class], ['GET']);
    $app->route('/api/authorization/roles/findAllByPaging', [...$auth, ...[Authorization\Handler\Roles\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/authorization/roles/findOneById/:roleId', [...$auth, ...[Authorization\Handler\Roles\FindOneByIdHandler::class]], ['GET']);

    // User Roles (private)
    $app->route('/api/authorization/userRoles/assign', [...$auth, ...[Authorization\Handler\UserRoles\AssignHandler::class]], ['PUT']);
    $app->route('/api/authorization/userRoles/unassign', [...$auth, ...[Authorization\Handler\UserRoles\UnassignHandler::class]], ['PUT']);
    $app->route('/api/authorization/userRoles/findAllByPaging/:roleId', [...$auth, ...[Authorization\Handler\UserRoles\FindAllByPagingHandler::class]], ['GET']);

    // Permissions (private)
    $app->route('/api/authorization/permissions/create', [...$auth, [Authorization\Handler\Permissions\CreateHandler::class]], ['POST']);
    $app->route('/api/authorization/permissions/copy/:permId', [...$auth, [Authorization\Handler\Permissions\CopyHandler::class]], ['POST']);
    $app->route('/api/authorization/permissions/update/:permId', [...$auth, [Authorization\Handler\Permissions\UpdateHandler::class]], ['PUT']);
    $app->route('/api/authorization/permissions/delete/:permId', [...$auth, [Authorization\Handler\Permissions\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/authorization/permissions/findAll', [JwtAuthenticationMiddleware::class, Authorization\Handler\Permissions\FindAllHandler::class], ['GET']);
    $app->route('/api/authorization/permissions/findAllByPaging', [...$auth, [Authorization\Handler\Permissions\FindAllByPagingHandler::class]], ['GET']);
};
