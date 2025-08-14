<?php

declare(strict_types=1);

namespace Modules\Handler;

use Common\Helper\ErrorWrapperInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Modules\Filter\DeleteFilter;
use Modules\Model\ModuleModelInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(DeleteFilter::class);

        return new DeleteHandler(
            $container->get(ModuleModelInterface::class),
            $inputFilter,
            $container->get(ErrorWrapperInterface::class)
        );
    }
}
