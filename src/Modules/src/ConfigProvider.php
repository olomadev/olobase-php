<?php

declare(strict_types=1);

namespace Modules;

use Mezzio\Application;
use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Olobase\Mezzio\ColumnFiltersInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;

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
            'dependencies' => $this->getDependencies(),
            'input_filters' => $this->getInputFilters(),
        ];
    }

    public function getDependencies() : array
    {
        return [
            'factories'  => [

                // modules
                Handler\CreateHandler::class => Handler\CreateHandlerFactory::class,
                Handler\UpdateHandler::class => Handler\UpdateHandlerFactory::class,
                Handler\DeleteHandler::class => Handler\DeleteHandlerFactory::class,
                Handler\FindAllHandler::class => Handler\FindAllHandlerFactory::class,
                Handler\FindAllByPagingHandler::class => Handler\FindAllByPagingHandlerFactory::class,

                // models
                Model\ModuleModelInterface::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $modules = new TableGateway('modules', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\ModuleModel($modules, $cacheStorage, $columnFilters);
                },
            ],
        ];
    }
    
    public function getInputFilters() : array
    {
        return [
            'factories' => [
                InputFilter\SaveFilter::class => InputFilter\SaveFilterFactory::class,
                InputFilter\DeleteFilter::class => InputFilter\DeleteFilterFactory::class,
            ]
        ];
    }

    public static function registerRoutes(Application $app, ContainerInterface $container): void
    {
        (require __DIR__ . '/../config/routes.php')($app, $container);
    }

}
