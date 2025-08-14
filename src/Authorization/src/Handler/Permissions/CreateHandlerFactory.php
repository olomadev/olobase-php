<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Laminas\InputFilter\InputFilterPluginManager;
use Olobase\Authorization\PermissionRepositoryInterface;
use Olobase\Validation\ValidationErrorFormatterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new CreateHandler(
            permissionRepository: $container->get(PermissionRepositoryInterface::class),
            filterManager: $container->get(InputFilterPluginManager::class),
            errorFormatter: $container->get(ValidationErrorFormatterInterface::class)
        );
    }
}
