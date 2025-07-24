<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Olobase\Authorization\Contracts\RoleModelInterface;
use Common\Helper\ValidationErrorFormatterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class CreateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);
        
        return new CreateHandler(
            $container->get(RoleModelInterface::class),
            $pluginManager,
            $container->get(ValidationErrorFormatterInterface::class)
        );
    }
}
