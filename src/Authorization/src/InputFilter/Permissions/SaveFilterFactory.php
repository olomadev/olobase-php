<?php

declare(strict_types=1);

namespace Authorization\InputFilter\Permissions;

use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class SaveFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SaveFilter(
            $container->get(AdapterInterface::class),
            $container->get(InputFilterPluginManager::class),
            $container->get(ServerRequestInterface::class)()
        );
    }
}
