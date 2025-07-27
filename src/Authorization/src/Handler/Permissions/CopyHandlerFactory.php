<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Olobase\Authorization\PermissionModelInterface;
use Common\Util\ErrorWrapperInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class CopyHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);

        return new CopyHandler(
            $container->get(PermissionModelInterface::class),
            $pluginManager,
            $container->get(ErrorWrapperInterface::class)
        );
    }
}
