<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Authentication\JwtAuth\TokenInterface;

class LogoutHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new LogoutHandler(
            token: $container->get(TokenInterface::class)
        );
    }
}
