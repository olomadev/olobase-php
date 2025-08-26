<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Modularity\Authorization\PermissionRepositoryInterface;

class FindAllHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindAllHandler($container->get(PermissionRepositoryInterface::class));
    }
}
