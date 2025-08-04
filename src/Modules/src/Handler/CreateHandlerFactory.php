<?php

declare(strict_types=1);

namespace Modules\Handler;

use Modules\Model\ModuleModelInterface;
use Modules\InputFilter\SaveFilter;
use Olobase\Validation\ValidationErrorFormatterInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class CreateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);

        return new CreateHandler(
            $container->get(ModuleModelInterface::class),
            $pluginManager,
            $container->get(ValidationErrorFormatterInterface::class)
        );
    }
}
