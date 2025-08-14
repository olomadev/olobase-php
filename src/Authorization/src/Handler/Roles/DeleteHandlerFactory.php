<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Laminas\InputFilter\InputFilterPluginManager;
use Olobase\Authorization\Contract\RoleModelInterface;
use Olobase\Util\ValidationErrorFormatterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);

        return new DeleteHandler(
            $container->get(RoleModelInterface::class),
            $pluginManager,
            $container->get(ValidationErrorFormatterInterface::class)
        );
    }
}
