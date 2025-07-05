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
    // Users (private)
    $app->route('/api/users/create', [...$auth, [Users\Handler\CreateHandler::class]], ['POST']);
    $app->route('/api/users/update/:userId', [...$auth, [Users\Handler\UpdateHandler::class]], ['PUT']);
    $app->route('/api/users/delete/:userId', [...$auth, [Users\Handler\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/users/updatePassword/:userId', [...$auth, [Users\Handler\UpdatePasswordHandler::class]], ['PUT']);
    $app->route('/api/users/findAll', [...$auth, [Users\Handler\FindAllHandler::class]], ['GET']);
    $app->route('/api/users/findAllByPaging', [...$auth, [Users\Handler\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/users/findOneById/:userId', [...$auth, [Users\Handler\FindOneByIdHandler::class]], ['GET']);
    
    // Account (private)
    $app->route('/api/users/myAccount/findMe', [...$auth, ...[Users\Handler\MyAccount\FindMeHandler::class]], ['GET']);
    $app->route('/api/users/myAccount/update', [...$auth, ...[Users\Handler\MyAccount\UpdateHandler::class]], ['PUT']);
    $app->route('/api/users/myAccount/updatePassword', [...$auth, ...[Users\Handler\MyAccount\UpdatePasswordHandler::class]], ['PUT']);
};
