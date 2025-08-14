<?php

declare(strict_types=1);

namespace Modules\Handler;

use Laminas\InputFilter\InputFilterPluginManager;
use Modules\Service\ModuleService;
use Olobase\Validation\ValidationErrorFormatterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new CreateHandler(
            moduleService: $container->get(ModuleService::class),
            filterManager: $container->get(InputFilterPluginManager::class),
            errorFormatter: $container->get(ValidationErrorFormatterInterface::class)
        );
    }
}
