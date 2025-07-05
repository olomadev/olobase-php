<?php

declare(strict_types=1);

namespace Authorization\Handler\UserRoles;

use Authorization\Model\UserRoleModelInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindAllByPagingHandler($container->get(UserRoleModelInterface::class));
    }
}
