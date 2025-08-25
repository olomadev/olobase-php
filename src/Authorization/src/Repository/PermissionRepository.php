<?php

declare(strict_types=1);

namespace Authorization\Repository;

use Authorization\Entity\Permission;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;
use Olobase\Authorization\PermissionRepositoryInterface;
use Olobase\DataTable\ColumnFiltersInterface;
use Olobase\Db\JsonExpressionHelper;
use Olobase\Repository\AbstractRepository;

use function array_map;
use function iterator_to_array;
use function json_encode;
use function strtolower;
use function strtoupper;
use function ucfirst;

class PermissionRepository extends AbstractRepository implements PermissionRepositoryInterface
{
    public function __construct(
        private TableGatewayInterface $permissions,
        private StorageInterface $cache,
        private ColumnFiltersInterface $columnFilters
    ) {
        parent::__construct($permissions, $cache);
    }

    public function findAll(): array
    {
        $key = APP_CACHE_PREFIX . self::class . ':' . __FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->permissions->getAdapter());
        $select = $sql->select();
        $select->columns([
            'id',
            'module',
            'name',
            'action',
            'route',
            'method',
        ]);
        $select->from(['p' => 'permissions']);
        $select->order(['module ASC', 'name ASC']);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results   = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();

        if (! empty($results)) {
            $this->cache->setItem($key, $results);
        }
        return $results;
    }

    public function findByRoleId(string $roleId): array
    {
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id',
            'module',
            'name',
            'action',
            'route',
            'method',
        ]);
        $select->from(['p' => 'permissions'])
            ->join(
                ['rp' => 'role_permissions'],
                'p.id = rp.perm_id',
                [],
                $select::JOIN_INNER
            )
            ->where(['rp.role_id' => $roleId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();
        $rows      = iterator_to_array($result);
        $statement->getResource()->closeCursor();

        return array_map(
            fn($r) => new Permission(
                $r['id'],
                $r['module'],
                $r['name'],
                json_encode(['id' => $r['action'], 'name' => ucfirst($r['action'])]),
                $r['route'],
                json_encode(['id' => $r['method'], 'name' => strtoupper($r['method'])]),
            ),
            $rows
        );
    }

    public function findGroupedByRole(): array
    {
        $key = APP_CACHE_PREFIX . self::class . ':' . __FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $select = $this->permissions->getSql()->select();
        $select->columns(['id', 'route', 'method', 'action']);
        $select->join(
            ['rp' => 'role_permissions'],
            'permissions.id = rp.perm_id',
            [],
            $select::JOIN_INNER
        );
        $select->join(
            ['r' => 'roles'],
            'r.id = rp.role_id',
            ['key', 'level'],
            $select::JOIN_LEFT
        );
        $resultSet = $this->permissions->selectWith($select);
        $results   = [];
        foreach ($resultSet as $row) {
            $results[$row['key']][] = $row['route'] . '^' . $row['method'];
        }
        // echo $select->getSqlString($this->permissions->getAdapter()->getPlatform());
        // die;
        if (! empty($results)) {
            $this->cache->setItem($key, $results);
        }
        return $results;
    }

    public function findByPaging(array $get): Paginator
    {
        $platform   = strtolower($this->adapter->getPlatform()->getName());
        $jsonHelper = new JsonExpressionHelper($platform);

        $sql    = new Sql($this->permissions->getAdapter());
        $select = $sql->select();
        $select->columns([
            'id',
            'action' => $jsonHelper->jsonObject([
                'id'   => 'p.action',
                'name' => $jsonHelper->ucfirst('p.action'),
            ]),
            'method' => $jsonHelper->jsonObject([
                'id'   => 'p.method',
                'name' => $jsonHelper->upper('p.method'),
            ]),
            'module',
            'name',
            'route',
        ])->from(['p' => 'permissions']);

        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'module',
            'name',
            'action',
            'route',
            'method',
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

        // echo $select->getSqlString($this->permissions->getAdapter()->getPlatform());
        // die;
        $paginatorAdapter = new DbSelect($select, $this->adapter);
        return new Paginator($paginatorAdapter);
    }

    protected function doCreate(object $entity)
    {
        $this->permissions->insert($entity->toSnakeCaseArray());
        return $entity->getId();
    }

    protected function doUpdate(object $entity)
    {
        return $this->permissions->update($entity->toSnakeCaseArray(['id']), ['id' => $entity->getId()]);
    }

    protected function doDelete(object $entity)
    {
        return $this->permissions->delete(['id' => $entity->getId()]);
    }

    protected function deleteCache(): void
    {
        $this->cache->removeItem(APP_CACHE_PREFIX . self::class . ':findGroupedByRole');
    }
}
