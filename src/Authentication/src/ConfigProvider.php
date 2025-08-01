<?php

declare(strict_types=1);

namespace Authentication;

use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Olobase\Router\AttributeRouteProviderInterface;
use Olobase\Authentication\Service\JwtAuthenticationInterface;
use Olobase\Authentication\Service\JwtEncoderInterface;
use Olobase\Authentication\Service\TokenInterface;
use Olobase\Authentication\Util\TokenEncryptHelper;
use Olobase\Authentication\Util\TokenEncryptHelperFactory;

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
            'aliases' => [
                AuthenticationInterface::class => JwtAuthenticationInterface::class,
            ],
            'factories' => [
                // authentication
                JwtAuthenticationInterface::class => Factory\JwtAuthenticationServiceFactory::class,
                JwtEncoderInterface::class => Factory\JwtEncoderServiceFactory::class,
                TokenInterface::class => Factory\TokenServiceFactory::class,

                // authorization
                \Mezzio\Authorization\AuthorizationInterface::class => \Olobase\Authorization\Service\AuthorizationFactory::class,

                // middlewares
                Middleware\JwtAuthenticationMiddleware::class => Middleware\JwtAuthenticationMiddlewareFactory::class,

                // helpers
                TokenEncryptHelper::class => TokenEncryptHelperFactory::class,

                // handlers
                Handler\TokenHandler::class => Handler\TokenHandlerFactory::class,
                Handler\RefreshHandler::class => Handler\RefreshHandlerFactory::class,
                Handler\LogoutHandler::class => Handler\LogoutHandlerFactory::class,
                Handler\SessionUpdateHandler::class => Handler\SessionUpdateHandlerFactory::class,

            ],
        ];
    }

    public static function registerRoutes(ContainerInterface $container): void
    {
        $provider = $container->get(AttributeRouteProviderInterface::class);
        $provider->registerRoutes(dirname(__DIR__));
    }
}
