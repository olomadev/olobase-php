<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Olobase\Authorization\PermissionRepositoryInterface;
use Olobase\Util\ValidationErrorFormatterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class UpdateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new UpdateHandler(
            permissionRepository: $container->get(PermissionRepositoryInterface::class),
            filterManager: $container->get(InputFilterPluginManager::class),
            errorFormatter: $container->get(ValidationErrorFormatterInterface::class)
        );
    }
}
