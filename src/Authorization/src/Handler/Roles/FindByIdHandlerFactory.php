<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Authorization\RoleRepositoryInterface;

class FindByIdHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindByIdHandler($container->get(RoleRepositoryInterface::class));
    }
}
