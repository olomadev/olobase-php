<?php

declare(strict_types=1);

namespace Authorization\Handler\UserRoles;

use Authorization\Model\UserRoleModelInterface;
use Authorization\InputFilter\UserRoles\UnassignRoleFilter;
use Common\Helper\ErrorWrapperInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class UnassignHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(UnassignRoleFilter::class);

        return new UnassignHandler(
            $container->get(UserRoleModelInterface::class),
            $inputFilter,
            $container->get(ErrorWrapperInterface::class)
        );
    }
}
