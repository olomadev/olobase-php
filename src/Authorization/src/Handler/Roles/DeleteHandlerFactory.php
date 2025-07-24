<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Olobase\Authorization\Contracts\RoleModelInterface;
use Common\Helper\ValidationErrorFormatterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

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
