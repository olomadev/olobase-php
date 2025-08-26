<?php

declare(strict_types=1);

use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Service\StorageAdapterFactoryInterface;
use Laminas\Cache\Storage\StorageInterface;
use Modularity\Cache\CacheStorageAdapter;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        'aliases' => [
            // Fully\Qualified\ClassOrInterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
            SimpleCacheInterface::class => function (ContainerInterface $container) {
                return new SimpleCacheDecorator($container->get(StorageInterface::class));
            },
            PredisClient::class         => function (ContainerInterface $container) {
                $config = $container->get('config')['redis'];
                return new PredisClient([
                    'scheme'   => 'tcp',
                    'host'     => $config['host'],
                    'port'     => $config['port'],
                    'password' => $config['password'],
                    'timeout'  => $config['timeout'],
                    // 'persistent' => '1',
                ]);
            },
            StorageInterface::class     => function (ContainerInterface $container) {
                $config         = $container->get('config')['redis'];
                $storageFactory = $container->get(StorageAdapterFactoryInterface::class);
                $storageConfig  = [
                    'adapter' => 'redis',
                    'options' => [
                        'ttl'       => 0, // 86400 = 24 hours, 3600 = 1 hour
                        'namespace' => '',
                        'server'    => [
                            'host'    => $config['host'],
                            'port'    => $config['port'],
                            'timeout' => $config['timeout'],
                        ],
                        'password'  => $config['password'],
                    ],
                    'plugins' => [
                        ['name' => 'serializer'],
                    ],
                ];
                $realStorage    = $storageFactory->createFromArrayConfiguration($storageConfig);
                $cacheEnabled   = $container->get('config')['cache_enabled'] ?? true;
                return new CacheStorageAdapter($realStorage, $cacheEnabled);
            },
        ],
    ],
];
