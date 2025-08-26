<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Laminas\InputFilter\InputFilterPluginManager;
use Modularity\Authorization\Contract\RoleModelInterface;
use Modularity\Util\ValidationErrorFormatterInterface;
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
