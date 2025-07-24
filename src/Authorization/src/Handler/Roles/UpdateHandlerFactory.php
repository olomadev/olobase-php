<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Common\Helper\ValidationErrorFormatterInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Olobase\Authorization\Contracts\RoleModelInterface;

class UpdateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);

        return new UpdateHandler(
            $container->get(RoleModelInterface::class),
            $pluginManager,
            $container->get(ValidationErrorFormatterInterface::class)
        );
    }
}
