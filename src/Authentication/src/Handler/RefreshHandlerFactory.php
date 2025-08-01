<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Olobase\Util\ValidationErrorFormatterInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RefreshHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new RefreshHandler(
            authentication: $container->get(AuthenticationInterface::class),
            errorFormatter: $container->get(ValidationErrorFormatterInterface::class)
        );
    }
}
