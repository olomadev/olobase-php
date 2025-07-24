<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Authorization\Contracts\RoleModelInterface;

class FindAllHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindAllHandler($container->get(RoleModelInterface::class));
    }
}
