<?php

declare(strict_types=1);

namespace Authentication;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Modularity\Authentication\JwtAuth\JwtAuthenticationInterface;
use Modularity\Authentication\JwtAuth\JwtEncoderInterface;
use Modularity\Authentication\JwtAuth\TokenInterface;
use Modularity\Authentication\Util\TokenEncryptHelper;
use Modularity\Authentication\Util\TokenEncryptHelperFactory;
use Modularity\Authorization\AuthorizationFactory;
use Modularity\Router\AttributeRouteProviderInterface;
use Psr\Container\ContainerInterface;

/**
 * The configuration provider for the Authentication module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'aliases'   => [
                AuthenticationInterface::class => JwtAuthenticationInterface::class,
            ],
            'factories' => [
                // authentication
                JwtAuthenticationInterface::class => Factory\JwtAuthenticationFactory::class,
                JwtEncoderInterface::class        => Factory\JwtEncoderFactory::class,
                TokenInterface::class             => Factory\TokenFactory::class,

                // authorization
                AuthorizationInterface::class => AuthorizationFactory::class,

                // middlewares
                Middleware\JwtAuthenticationMiddleware::class => Middleware\JwtAuthenticationMiddlewareFactory::class,

                // helpers
                TokenEncryptHelper::class => TokenEncryptHelperFactory::class,

                // handlers
                Handler\TokenHandler::class         => Handler\TokenHandlerFactory::class,
                Handler\RefreshHandler::class       => Handler\RefreshHandlerFactory::class,
                Handler\LogoutHandler::class        => Handler\LogoutHandlerFactory::class,
                Handler\SessionUpdateHandler::class => Handler\SessionUpdateHandlerFactory::class,
            ],
        ];
    }

    public static function registerRoutes(ContainerInterface $container, ?string $moduleName = null): void
    {
        $provider = $container->get(AttributeRouteProviderInterface::class);
        $provider->registerRoutes($moduleName);
    }
}
