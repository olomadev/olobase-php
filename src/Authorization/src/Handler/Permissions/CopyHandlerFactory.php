<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authorization\Model\PermissionModel;
use Authorization\Filter\Permissions\SaveFilter;
use Olobase\Mezzio\DataManagerInterface;
use Olobase\Mezzio\Authorization\PermissionModelInterface;
use Common\Helper\ErrorWrapperInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class CopyHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(SaveFilter::class);

        return new CopyHandler(
            $container->get(PermissionModelInterface::class),
            $container->get(DataManagerInterface::class),
            $inputFilter,
            $container->get(ErrorWrapperInterface::class)
        );
    }
}
