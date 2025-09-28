<?php

declare(strict_types=1);

namespace Modules;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Modularity\DataTable\ColumnFiltersInterface;
use Modularity\Router\AttributeRouteProviderInterface;
use Psr\Container\ContainerInterface;

use function dirname;

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
    public function __invoke(): array
    {
        return [
            'dependencies'  => $this->getDependencies(),
            'input_filters' => $this->getInputFilters(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [

                // handler
                Handler\CreateHandler::class          => Handler\CreateHandlerFactory::class,
                Handler\UpdateHandler::class          => Handler\UpdateHandlerFactory::class,
                Handler\DeleteHandler::class          => Handler\DeleteHandlerFactory::class,
                Handler\FindAllHandler::class         => Handler\FindAllHandlerFactory::class,
                Handler\FindAllByPagingHandler::class => Handler\FindAllByPagingHandlerFactory::class,

                // respository
                Repository\ModuleRepositoryInterface::class => function ($container) {
                    $dbAdapter     = $container->get(AdapterInterface::class);
                    $modules       = new TableGateway('modules', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage  = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Repository\ModuleRepository($modules, $cacheStorage, $columnFilters);
                },
            ],
        ];
    }

    public function getInputFilters(): array
    {
        return [
            'factories' => [
                InputFilter\SaveFilter::class   => InputFilter\SaveFilterFactory::class,
                InputFilter\DeleteFilter::class => InputFilter\DeleteFilterFactory::class,
            ],
        ];
    }

    public static function registerRoutes(ContainerInterface $container, ?string $moduleName = null): void
    {
        $provider = $container->get(AttributeRouteProviderInterface::class);
        $provider->registerRoutes(dirname(__DIR__), $moduleName);
    }

}
