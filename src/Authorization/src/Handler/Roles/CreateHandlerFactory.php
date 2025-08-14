<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Laminas\InputFilter\InputFilterPluginManager;
use Olobase\Authorization\Contract\RoleRepositoryInterface;
use Olobase\Util\ValidationErrorFormatterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new CreateHandler(
            roleRepository: $container->get(RoleRepositoryInterface::class),
            filterManager: $container->get(InputFilterPluginManager::class),
            errorFormatter: $container->get(ValidationErrorFormatterInterface::class)
        );
    }
}
