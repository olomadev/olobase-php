<?php

declare(strict_types=1);

namespace Authorization\Repository;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;
use Olobase\Authorization\PermissionRepositoryInterface;
use Olobase\DataTable\ColumnFiltersInterface;
use Olobase\Repository\AbstractRepository;

use function iterator_to_array;

class PermissionRepository extends AbstractRepository implements PermissionRepositoryInterface
{
    public function __construct(
        private TableGatewayInterface $permissions,
        private StorageInterface $cache,
        private ColumnFiltersInterface $columnFilters
    ) {
        parent::__construct($permissions, $cache);
    }

    public function findPermissions(): array
    {
        // $key = APP_CACHE_PREFIX . self::class . ':findPermissions';
        // if ($this->cache->hasItem($key)) {
        //     return $this->cache->getItem($key);
        // }
        $select = $this->permissions->getSql()->select();
        $select->columns(['id','route','method','action']);
        $select->join(
            ['rp' => 'role_permissions'], 'permissions.id = rp.perm_id', [],
            $select::JOIN_INNER
        );
        $select->join(
            ['r' => 'roles'], 'r.id = rp.role_id', ['key', 'level'],
            $select::JOIN_LEFT
        );
        $resultSet = $this->permissions->selectWith($select);
        $results   = [];
        foreach ($resultSet as $row) {
            $results[$row['key']][] = $row['route'] . '^' . $row['method'];
        }
        // echo $select->getSqlString($this->permissions->getAdapter()->getPlatform());
        // die;

        // if (! empty($results)) {
        //     $this->cache->setItem($key, $results);
        // }
        return $results;
    }

    public function findAllPermissions(): array
    {
        $sql    = new Sql($this->permissions->getAdapter());
        $select = $sql->select();
        $select->columns([
            'id','module','name','action','route','method',
        ]);
        $select->from(['p' => 'permissions']);
        $select->order(['module ASC', 'name ASC']);

        $statement   = $sql->prepareStatementForSqlObject($select);
        $resultSet   = $statement->execute();
        $permissions = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();
        return $permissions;
    }

    public function findAllByPaging(array $get): Paginator
    {
        $sql    = new Sql($this->permissions->getAdapter());
        $select = $sql->select();
        $select->columns([
            'id',
            'action' => new Expression("JSON_OBJECT('id', p.action, 'name', CONCAT(UPPER(SUBSTRING(p.action, 1, 1)), LOWER(SUBSTRING(p.action, 2))))"),
            'method' => new Expression("JSON_OBJECT('id', p.method, 'name', p.method)"),
            'module',
            'name',
            'route',
        ])->from(['p' => 'permissions']);

        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'module','name','action','route','method',
        ]);
        $this->columnFilters->setSelect($select);
        $this->columnFilters->setData($get);

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
                $select->order($order);
            }
        } else {
            $select->order(['module ASC', 'name ASC']);
        }

        $paginatorAdapter = new DbSelect($select, $this->adapter);
        return new Paginator($paginatorAdapter);
    }

    protected function doCreate(object $entity)
    {
        $this->permissions->insert($entity->toArray());
        return $entity->getId();
    }

    protected function doUpdate(object $entity)
    {
        return $this->permissions->update($entity->toArray(['id']), ['id' => $entity->getId()]);
    }

    protected function doDelete(int|string $id)
    {
        return $this->permissions->delete(['id' => $id]);
    }

    protected function deleteCache(): void
    {
        $this->cache->removeItem(APP_CACHE_PREFIX . self::class . ':findPermissions');
    }
}
