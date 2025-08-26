<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Modularity\Authentication\JwtAuth\TokenInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogoutHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new LogoutHandler(
            token: $container->get(TokenInterface::class)
        );
    }
}
