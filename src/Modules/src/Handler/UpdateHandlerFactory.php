<?php

declare(strict_types=1);

namespace Modules\Handler;

use Common\Helper\ErrorWrapperInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Modules\InputFilter\SaveFilter;
use Modules\Model\ModuleModelInterface;
use Olobase\DataTable\DataManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
