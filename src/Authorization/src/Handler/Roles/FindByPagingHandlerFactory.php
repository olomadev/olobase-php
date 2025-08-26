<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Modularity\Authorization\Contract\RoleModelInterface;

class FindByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindByPagingHandler($container->get(RoleModelInterface::class));
    }
}
