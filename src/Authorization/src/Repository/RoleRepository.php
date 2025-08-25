<?php

declare(strict_types=1);

namespace Authorization\Repository;

use Authorization\Entity\Role;
use Authorization\Repository\PermissionRepository;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;
use Olobase\Authorization\PermissionRepositoryInterface;
use Olobase\Authorization\RoleRepositoryInterface;
use Olobase\DataTable\ColumnFiltersInterface;
use Olobase\Repository\AbstractRepository;

use function array_column;
use function array_diff;
use function in_array;
use function iterator_to_array;

class RoleRepository extends AbstractRepository implements RoleRepositoryInterface
{
    public function __construct(
        private TableGatewayInterface $roles,
        private TableGatewayInterface $rolePermissions,
        private TableGatewayInterface $userRoles,
        private StorageInterface $cache,
        private PermissionRepositoryInterface $permissionRepo,
        private ColumnFiltersInterface $columnFilters,
    ) {
        parent::__construct($roles, $cache);
    }

    public function findAll(): ?array
    {
        $key = APP_CACHE_PREFIX . self::class . ':' . __FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->roles->getAdapter());
        $select = $sql->select();
        $select->columns(['id', 'name']);
        $select->from(['r' => 'roles']);
        $select->order(['level ASC']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $results   = iterator_to_array($statement->execute());
        if (! empty($results)) {
            $this->cache->setItem($key, $results);
        }
        return $results;
    }

    public function findByUserId(string $userId): array
    {
        $sql    = new Sql($this->roles->getAdapter());
        $select = $sql->select();
        $select->from(['r' => 'roles']);
        $select->columns(['key']);
        $select->join(['ru' => 'user_roles'], 'r.id = ru.role_id', ['user_id'], $select::JOIN_LEFT);
        $select->where(['ru.user_id' => $userId]);
        $roles = [];
        foreach ($sql->prepareStatementForSqlObject($select)->execute() as $row) {
            $roles[] = $row['key'];
        }
        return $roles;
    }

    public function findByKey(string $roleKey): ?array
    {
        $key = self::class . ':' . __FUNCTION__ . ':' . $roleKey;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $select = $this->roles->getSql()->select();
        $select->columns(['id', 'key', 'level']);
        $select->where(['key' => $roleKey]);
        $row = $this->roles->selectWith($select)->current();
        if ($row) {
            $this->cache->setItem($key, $row);
        }
        return $row ?: null;
    }

    public function findKeys(): array
    {
        $data = [];
        foreach ($this->roles->select() as $row) {
            $data[] = ['id' => $row['key'], 'name' => $row['key']];
        }
        return $data;
    }

    public function findLevels(): array
    {
        $levels = [];
        foreach ($this->roles->select() as $row) {
            $levels[$row['key']] = $row['level'];
        }
        return $levels;
    }

    public function findByPaging(array $get): Paginator
    {
        $sql    = new Sql($this->roles->getAdapter());
        $select = $sql->select('roles')
            ->columns(['id', 'key', 'name', 'level']);

        $this->columnFilters->clear();
        $this->columnFilters->setColumns(['key', 'name', 'level']);
        $this->columnFilters->setData($get);
        $this->columnFilters->setSelect($select);

        if ($this->columnFilters->searchDataIsNotEmpty()) {
            $nest = $select->where->nest();
            foreach ($this->columnFilters->getSearchData() as $col => $words) {
                $nest = $nest->or->nest();
                foreach ($words as $str) {
                    $nest->or->like(new Expression($col), '%' . $str . '%');
                }
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }

        if ($this->columnFilters->orderDataIsNotEmpty()) {
            foreach ($this->columnFilters->getOrderData() as $order) {
                $select->order(new Expression($order));
            }
        }
        return new Paginator(new DbSelect($select, $this->roles->getAdapter()));
    }

    public function findById(string $roleId): ?Role
    {
        $sql    = new Sql($this->roles->getAdapter());
        $select = $sql->select();
        $select->columns(['id', 'key', 'name', 'level'])
            ->from(['r' => 'roles'])
            ->where(['r.id' => $roleId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row       = $resultSet->current();
        $statement->getResource()->closeCursor();

        if (! $row) {
            return null;
        }
        $permissions = $this->permissionRepo->findByRoleId($roleId);

        return new Role(
            $row['id'],
            $row['key'],
            $row['name'],
            $row['level'],
            $permissions,
        );
    }

    /** @inheritDoc */
    protected function doCreate(object $entity)
    {
        $this->roles->insert($entity->toSnakeCaseArray(['rolePermissions']));

        $data = [];
        foreach ($entity->getRolePermissions() as $val) {
            $data['role_id'] = $entity->getId();
            $data['perm_id'] = $val['id'];
            $this->rolePermissions->insert($data);
        }
        return $entity->getId();
    }

    protected function doUpdate(object $entity)
    {
        $roleId = $entity->getId();
        $this->roles->update($entity->toSnakeCaseArray(['id', 'rolePermissions']), ['id' => $roleId]);

        $existing    = $this->rolePermissions->fetchAll(['role_id' => $roleId]);
        $existingIds = array_column($existing, 'perm_id');
        $newIds      = array_column($entity->getRolePermissions(), 'id');

        // Deleted items
        $toDelete = array_diff($existingIds, $newIds);
        if (! empty($toDelete)) {
            $this->rolePermissions->delete([
                'role_id' => $roleId,
                'perm_id' => $toDelete,
            ]);
        }

        // To be added
        $toInsert = array_diff($newIds, $existingIds);
        foreach ($entity->getRolePermissions() as $perm) {
            if (in_array($perm['id'], $toInsert, true)) {
                $this->rolePermissions->insert([
                    'role_id' => $roleId,
                    'perm_id' => $perm['id'],
                ]);
            }
        }
        return $roleId;
    }

    /** @inheritDoc */
    protected function doDelete(object $entity)
    {
        return $this->roles->delete(['id' => $entity->getId()]);
    }

    protected function deleteCache(): void
    {
        $this->cache->removeItem(APP_CACHE_PREFIX . self::class . ':findAll');
        $this->cache->removeItem(APP_CACHE_PREFIX . PermissionRepository::class . ':findPermissions');
    }
}
