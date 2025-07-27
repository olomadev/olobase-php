<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Authorization\Contract\RoleModelInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindAllByPagingHandler($container->get(RoleModelInterface::class));
    }
}
