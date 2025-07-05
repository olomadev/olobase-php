<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authorization\Model\PermissionModel;
use Authorization\InputFilter\Permissions\DeleteFilter;
use Common\Helper\ErrorWrapperInterface;
use Olobase\Mezzio\Authorization\PermissionModelInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class DeleteHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(DeleteFilter::class);

        return new DeleteHandler(
            $container->get(PermissionModelInterface::class),
            $inputFilter,
            $container->get(ErrorWrapperInterface::class)
        );
    }
}
