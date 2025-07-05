<?php

declare(strict_types=1);

namespace Authentication;

use Mezzio\Application;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Olobase\Mezzio\ColumnFiltersInterface;
use Olobase\Mezzio\Authentication\JwtEncoderInterface;
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
                AuthenticationInterface::class => Authentication\JwtAuthentication::class,
            ],
            'factories' => [
                // classes
                Authentication\JwtAuthentication::class => Authentication\JwtAuthenticationFactory::class,

                // middlewares
                Middleware\JwtAuthenticationMiddleware::class => Middleware\JwtAuthenticationMiddlewareFactory::class,
                
                // helpers
                Helper\TokenEncryptHelper::class => Helper\TokenEncryptHelperFactory::class,

                // handlers
                Handler\TokenHandler::class => Handler\TokenHandlerFactory::class,
                Handler\RefreshHandler::class => Handler\RefreshHandlerFactory::class,
                Handler\LogoutHandler::class => Handler\LogoutHandlerFactory::class,
                Handler\SessionUpdateHandler::class => Handler\SessionUpdateHandlerFactory::class,

                // models
                Model\TokenModelInterface::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $tokenEncrypt = $container->get(Helper\TokenEncryptHelper::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $jwtEncoder = $container->get(JwtEncoderInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null);
                    return new Model\TokenModel($container->get('config'), $cacheStorage, $tokenEncrypt, $jwtEncoder, $users);
                }
            ],
        ];
    }

    /**
     * Registers routes for the module
     */
    public static function registerRoutes(Application $app, ContainerInterface $container): void
    {
        (require __DIR__ . '/../config/routes.php')($app, $container);
    }
}
