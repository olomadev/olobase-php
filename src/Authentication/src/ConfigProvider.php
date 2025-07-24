<?php

declare(strict_types=1);

namespace Authentication;

use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Olobase\ColumnFiltersInterface;
use Olobase\Router\AttributeRouteProviderInterface;
use Olobase\Authentication\Service\JwtAuthentication;
use Olobase\Authentication\Service\JwtEncoderInterface;
use Olobase\Authentication\Service\TokenServiceInterface;
use Olobase\Authentication\Helper\TokenEncryptHelper;
use Olobase\Authentication\Helper\TokenEncryptHelperFactory;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

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
            'authentication' => [
                'adapter' => [
                    'type' => \Laminas\Authentication\Adapter\DbTable\CallbackCheckAdapter::class,
                    'options' => [
                        'table' => 'users',
                        'identity_column' => 'email',
                        'credential_column' => 'password',
                    ],
                ],
                'form' => [
                    'username' => 'username',
                    'password' => 'password',
                ],
            ],
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
                AuthenticationInterface::class => JwtAuthentication::class,
            ],
            'factories' => [
                // authorication
                \Mezzio\Authorization\AuthorizationInterface::class => \Olobase\Authorization\Service\AuthorizationFactory::class,

                // services
                JwtAuthentication::class => Authentication\JwtAuthenticationFactory::class,
                JwtEncoderInterface::class => Authentication\JwtEncoderFactory::class,
                TokenServiceInterface::class => Authentication\TokenServiceFactory::class,

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
