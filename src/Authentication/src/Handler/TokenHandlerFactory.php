<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Olobase\Validation\ValidationErrorFormatterInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class TokenHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new TokenHandler(
            config: $container->get('config'),
            pluginManager: $container->get(InputFilterPluginManager::class),
            authentication: $container->get(AuthenticationInterface::class),
            errorFormatter: $container->get(ValidationErrorFormatterInterface::class)
        );

    }
}
