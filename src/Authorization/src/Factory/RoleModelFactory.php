<?php

declare(strict_types=1);

namespace Authorization\Factory;

use Authorization\Model\RoleModel;
use Olobase\Mezzio\Authorization\RoleModelInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Cache\Storage\StorageInterface;
use Common\Contracts\ColumnFiltersInterface;
use Psr\Container\ContainerInterface;

class RoleModelFactory
{
    public function __invoke(ContainerInterface $container): RoleModelInterface
    {
        $dbAdapter = $container->get(AdapterInterface::class);
        $cacheStorage = $container->get(StorageInterface::class);
        $columnFilters = $container->get(ColumnFiltersInterface::class);

        $roles = new TableGateway('roles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
        $rolePermissions = new TableGateway('role_rermissions', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
        $userRoles = new TableGateway('user_roles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));

        return new RoleModel($roles, $rolePermissions, $userRoles, $cacheStorage, $columnFilters);
    }
}
