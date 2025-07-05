<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authorization\InputFilter\Permissions\SaveFilter;
use Olobase\Mezzio\DataManagerInterface;
use Olobase\Mezzio\Authorization\PermissionModelInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class UpdateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(SaveFilter::class);

        return new UpdateHandler(
            $container->get(PermissionModelInterface::class),
            $container->get(DataManagerInterface::class),
            $inputFilter,
            $container->get(Error::class)
        );
    }
}
