<?php

declare(strict_types=1);

namespace Authorization\InputFilter\UserRoles;

use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UnassignRoleFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new UnassignRoleFilter(
            $container->get(AdapterInterface::class),
            $container->get(InputFilterPluginManager::class)
        );
    }
}
