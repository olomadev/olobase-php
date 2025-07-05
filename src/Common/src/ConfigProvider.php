<?php

declare(strict_types=1);

namespace Common;

use Mezzio\Application;
use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Cache\Storage\StorageInterface;
use Predis\ClientInterface as PredisInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

/**
 * The configuration provider for the Authorization module
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
    public function __invoke() : array
    {
        return [
            'data_manager' => [
                'common_schema_module' => 'Common',
            ],
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'invokables' => [

            ],
            'factories'  => [
                // classes
                StorageInterface::class => Factory\CacheFactory::class,
                SimpleCacheInterface::class => Factory\SimpleCacheFactory::class,   
                PredisInterface::class => Factory\PredisFactory::class,
                Helper\ErrorWrapperInterface::class => Factory\ErrorWrapperFactory::class,

                // middlewares
                Middleware\SetLocaleMiddleware::class => Middleware\SetLocaleMiddlewareFactory::class,
                Middleware\JsonBodyParserMiddleware::class => Middleware\JsonBodyParserMiddlewareFactory::class,
            ],
        ];
    }
    
}
