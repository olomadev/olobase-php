<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authentication\AuthenticationInterface;
use Modularity\Validation\ErrorFormatterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TokenHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new TokenHandler(
            filterManager: $container->get(InputFilterPluginManager::class),
            authentication: $container->get(AuthenticationInterface::class),
            errorFormatter: $container->get(ErrorFormatterInterface::class)
        );
    }
}
