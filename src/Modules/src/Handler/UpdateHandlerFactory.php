<?php

declare(strict_types=1);

namespace Modules\Handler;

use Modules\Model\ModuleModelInterface;
use Modules\InputFilter\SaveFilter;
use Olobase\Mezzio\DataManagerInterface;
use Common\Helper\ErrorWrapperInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class UpdateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(SaveFilter::class);

        return new UpdateHandler(
            $container->get(ModuleModelInterface::class),
            $container->get(DataManagerInterface::class),
            $inputFilter,
            $container->get(ErrorWrapperInterface::class)
        );
    }
}
