<?php

declare(strict_types=1);

namespace Users;

use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Olobase\DataTable\ColumnFiltersInterface;
use Olobase\Router\AttributeRouteProviderInterface;
use Olobase\Authorization\PermissionModelInterface;
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
            'dependencies' => $this->getDependencies(),
            'input_filters' => $this->getInputFilters(),
        ];
    }

    public function getDependencies() : array
    {
        return [
            'factories'  => [

                // handlers
                Handler\CreateHandler::class => Handler\CreateHandlerFactory::class,
                Handler\UpdateHandler::class => Handler\UpdateHandlerFactory::class,
                Handler\DeleteHandler::class => Handler\DeleteHandlerFactory::class,
                Handler\FindOneByIdHandler::class => Handler\FindOneByIdHandlerFactory::class,
                Handler\FindAllHandler::class => Handler\FindAllHandlerFactory::class,
                Handler\FindAllByPagingHandler::class => Handler\FindAllByPagingHandlerFactory::class,
                
                // handlers - my account
                Handler\MyAccount\FindMeHandler::class => Handler\MyAccount\FindMeHandlerFactory::class,
                Handler\MyAccount\UpdateHandler::class => Handler\MyAccount\UpdateHandlerFactory::class,
                Handler\MyAccount\UpdatePasswordHandler::class => Handler\MyAccount\UpdatePasswordHandlerFactory::class,

                // models
                Model\UserModelInterface::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $userAvatars = new TableGateway('userAvatars', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $userRoles = new TableGateway('userRoles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $cache = $container->get(StorageInterface::class);
                    return new Model\UserModel(
                        $users,
                        $userAvatars,
                        $userRoles,
                        $cache,
                        $columnFilters,
                    );
                },
            ],
        ];
    }

    public function getInputFilters() : array
    {
        return [
            'factories' => [
                // Users
                InputFilter\SaveFilter::class => InputFilter\SaveFilterFactory::class,
                InputFilter\DeleteFilter::class => InputFilter\DeleteFilterFactory::class,

                // My Account
                InputFilter\MyAccount\SaveFilter::class => InputFilter\MyAccount\SaveFilterFactory::class,
                InputFilter\MyAccount\DeleteFilter::class => InputFilter\MyAccount\DeleteFilterFactory::class,
            ]
        ];
    }
    
    public static function registerRoutes(ContainerInterface $container): void
    {
        $provider = $container->get(AttributeRouteProviderInterface::class);
        $provider->registerRoutes(dirname(__DIR__));
    }

}
