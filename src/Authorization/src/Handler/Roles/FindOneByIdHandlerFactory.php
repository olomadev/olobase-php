<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Olobase\Mezzio\Authorization\RoleModelInterface;
use Olobase\Mezzio\DataManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindOneByIdHandler(
            $container->get(RoleModelInterface::class),
            $container->get(DataManagerInterface::class)
        );
    }
}
