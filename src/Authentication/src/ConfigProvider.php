<?php

declare(strict_types=1);

namespace Authentication;

use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Olobase\Mezzio\ColumnFiltersInterface;
use Olobase\Mezzio\Authentication\Service\JwtAuthentication;
use Olobase\Mezzio\Authentication\Service\JwtEncoderInterface;
use Olobase\Mezzio\Authentication\Service\TokenServiceInterface;
use Olobase\Mezzio\Authentication\Helper\TokenEncryptHelper;
use Olobase\Mezzio\Authentication\Helper\TokenEncryptHelperFactory;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Olobase\Mezzio\Authorization\RoleModelInterface;
use Authentication\Model\NullRoleModel;
use Common\Router\AttributeRouteProviderInterface;
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

                RoleModelInterface::class => function ($container) {
                    if ($container->has(\Authorization\Model\RoleModel::class)) {
                        return $container->get(\Authorization\Model\RoleModel::class);
                    }
                    return new NullRoleModel();
                },
            ],
        ];
    }

    public static function registerRoutes(ContainerInterface $container): void
    {
        $provider = $container->get(AttributeRouteProviderInterface::class);
        $provider->registerRoutes(dirname(__DIR__));
    }
}
