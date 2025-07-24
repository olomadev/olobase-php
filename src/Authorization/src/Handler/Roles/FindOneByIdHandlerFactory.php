<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Authorization\Contracts\RoleModelInterface;

class FindOneByIdHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindOneByIdHandler($container->get(RoleModelInterface::class));
    }
}
