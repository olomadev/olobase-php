<?php

declare(strict_types=1);

namespace Authentication\Middleware;

use Mezzio\Authentication\Exception;
use Mezzio\Authentication\AuthenticationInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class JwtAuthenticationMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : JwtAuthenticationMiddleware
    {
        $authentication = $container->has(AuthenticationInterface::class) ? $container->get(AuthenticationInterface::class) : null;
        if (null === $authentication) {
            throw new Exception\InvalidConfigException(
                'AuthenticationInterface service is missing'
            );
        }
        return new JwtAuthenticationMiddleware(
            $container->get('config'), 
            $authentication
        );
    }
}