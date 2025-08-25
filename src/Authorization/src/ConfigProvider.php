<?php

declare(strict_types=1);

namespace Authorization;

use Authorization\Repository\UserRoleRepositoryInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Olobase\DataTable\ColumnFiltersInterface;
use Olobase\Router\AttributeRouteProviderInterface;
use Psr\Container\ContainerInterface;
use Olobase\Authorization\PermissionRepositoryInterface;

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
            'dependencies' => $this->getDependencies(),
            'translator'   => $this->getTranslations(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'invokables' => [],
            'aliases'    => [],
            'factories'  => [

                // handlers - roles
                Handler\Roles\CreateHandler::class       => Handler\Roles\CreateHandlerFactory::class,
                Handler\Roles\UpdateHandler::class       => Handler\Roles\UpdateHandlerFactory::class,
                Handler\Roles\DeleteHandler::class       => Handler\Roles\DeleteHandlerFactory::class,
                Handler\Roles\FindByIdHandler::class     => Handler\Roles\FindByIdHandlerFactory::class,
                Handler\Roles\FindAllHandler::class      => Handler\Roles\FindHandlerFactory::class,
                Handler\Roles\FindByPagingHandler::class => Handler\Roles\FindByPagingHandlerFactory::class,

                // handlers - user roles
                Handler\UserRoles\AssignHandler::class       => Handler\UserRoles\AssignHandlerFactory::class,
                Handler\UserRoles\UnassignHandler::class     => Handler\UserRoles\UnassignHandlerFactory::class,
                Handler\UserRoles\FindByPagingHandler::class => Handler\UserRoles\FindByPagingHandlerFactory::class,

                // handlers - permissions
                Handler\Permissions\CreateHandler::class       => Handler\Permissions\CreateHandlerFactory::class,
                Handler\Permissions\UpdateHandler::class       => Handler\Permissions\UpdateHandlerFactory::class,
                Handler\Permissions\DeleteHandler::class       => Handler\Permissions\DeleteHandlerFactory::class,
                Handler\Permissions\FindAllHandler::class      => Handler\Permissions\FindAllHandlerFactory::class,
                Handler\Permissions\FindByPagingHandler::class => Handler\Permissions\FindByPagingHandlerFactory::class,

                // respositories
                UserRoleRepositoryInterface::class     => function ($container) {
                    $dbAdapter     = $container->get(AdapterInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $userRoles     = new TableGateway('user_roles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\UserRoleModel($userRoles, $columnFilters);
                },
                Repository\RoleRepository::class       => function ($container) {
                    $dbAdapter       = $container->get(AdapterInterface::class);
                    $cacheStorage    = $container->get(StorageInterface::class);
                    $permissionRepo  = $container->get(PermissionRepositoryInterface::class);
                    $columnFilters   = $container->get(ColumnFiltersInterface::class);
                    $roles           = new TableGateway('roles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $rolePermissions = new TableGateway('role_rermissions', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $userRoles       = new TableGateway('user_roles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Repository\RoleRepository($roles, $rolePermissions, $userRoles, $cacheStorage, $permissionRepo, $columnFilters);
                },
                Repository\PermissionRepository::class => function ($container) {
                    $dbAdapter     = $container->get(AdapterInterface::class);
                    $cacheStorage  = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $permissions   = new TableGateway('permissions', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Repository\PermissionRepository($permissions, $cacheStorage, $columnFilters);
                },
            ],
        ];
    }

    public function getTranslations(): array
    {
        return [
            'translation_file_patterns' => [
                [
                    'type'     => 'PhpArray',
                    'base_dir' => __DIR__ . '/i18n',
                    'pattern'  => '%s/messages.php',
                ],
            ],
        ];
    }

    public static function registerRoutes(ContainerInterface $container): void
    {
        $provider = $container->get(AttributeRouteProviderInterface::class);
        $provider->registerRoutes(dirname(__DIR__));
    }
}
