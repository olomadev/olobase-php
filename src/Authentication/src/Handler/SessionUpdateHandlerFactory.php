<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Laminas\Cache\Storage\StorageInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionUpdateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new SessionUpdateHandler(
            config: $container->get('config'),
            cache: $container->get(StorageInterface::class)
        );
    }
}
