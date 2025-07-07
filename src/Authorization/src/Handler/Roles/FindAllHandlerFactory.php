<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Olobase\Mezzio\Authorization\RoleModelInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindAllHandler($container->get(RoleModelInterface::class));
    }
}
