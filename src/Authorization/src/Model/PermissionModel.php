<?php

declare(strict_types=1);

namespace Authorization\Model;

use Exception;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Common\Helper\RandomStringHelper;
use Olobase\DataTable\ColumnFiltersInterface;
use Olobase\Authorization\PermissionModelInterface;

class PermissionModel implements PermissionModelInterface
{
    private $conn;
    private $adapter;

    public function __construct(
        private TableGatewayInterface $permissions,
        private StorageInterface $cache,
        private ColumnFiltersInterface $columnFilters
    )
    {
        $this->adapter = $permissions->getAdapter();
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    /**
     * Authorization permissions method
     * 
     * @see Olobase\
Authorization\PermissionModelInterface;
     * @return array
     */
    public function findPermissions() : array
    {
        $key = APP_CACHE_PREFIX.Self::class.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $adapter = $this->permissions->getAdapter();
        $select  = $this->permissions->getSql()->select();
        $select->columns([
            'id',
            'route',
            'method',
            'action',
        ]);
        $select->join(
            ['rp' => 'role_permissions'],
            'permissions.id = rp.perm_id', [], $select::JOIN_INNER);
        $select->join(
            ['r' => 'roles'],
            'r.id = rp.role_id', ['key','level'], $select::JOIN_LEFT);
        
        // echo $select->getSqlString($adapter->getPlatform());
        // die;
        $resultSet = $this->permissions->selectWith($select);
        $results = array();
        foreach ($resultSet as $row) {
            $results[$row['key']][] = $row['route'].'^'.$row['method'];
        }
        if (! empty($results)) {
            $this->cache->setItem($key, $results);
        }
        return $results;
    }

    public function findAllPermissions()
    {
        $sql = new Sql($this->adapter);
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
        $permissions = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();
        return $permissions;
    }

    public function findAllBySelect()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id',
            // 'groupName' => 'moduleName',
            'action' => new Expression("JSON_OBJECT('id', p.action, 'name', CONCAT(UPPER(SUBSTRING(p.action, 1, 1)), LOWER(SUBSTRING(p.action, 2))))"),
            'method' => new Expression("JSON_OBJECT('id', p.method, 'name', p.method)"),
            'module',
            'name',
            'route',
        ]);
        $select->from(['p' => 'permissions']);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAllBySelect();
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
                    $nest->or->like(new Expression($col), '%'.$str.'%');
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
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $paginatorAdapter = new DbSelect(
            $select,
            $this->adapter
        );
        $paginator = new Paginator($paginatorAdapter);
        return $paginator;
    }

    public function findOneById(string $roleId)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id',
            'name',
        ]);
        $select->from(['r' => 'roles']);
        $select->where(['r.id' => $roleId]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();

        // role permissions
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            'id',
            'module',
            'name',
            'action',
            'route',
            'method',
        );
        $select->from(['p' => 'permissions']);
        $select->join(['rp' => 'role_permissions'], 'p.id = rp.perm_id',
            [],
        $select::JOIN_LEFT);
        $select->where(['rp.role_id' => $roleId]);
         
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $rolePermissions = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();

        $row['rolePermissions'] = $rolePermissions;
        return $row;
    }    

    public function create(array $data)
    {
        try {
            $this->conn->beginTransaction();
            $this->permissions->insert($data['permissions']);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        try {
            $this->conn->beginTransaction();
            $this->permissions->update($data['permissions'], ['id' => $data['id']]);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function buildCreateDataFromPermission(string $permId) : array
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(['r' => 'permissions']);
        $select->where(['id' => $permId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();

        $post = [];
        if ($row) {
            $post = [
                'id' => RandomStringHelper::generateUuid(), // create new id
                'module' => $row['module'],
                'name' => $row['name'],
                'action' => ['id' => $row['action']],
                'route' => $row['route'],
                'method' => ['id' => $row['method']],
            ];
        }
        $this->deleteCache();
        return $post;
    }

    public function delete(string $permId)
    {
        try {
            $this->conn->beginTransaction();
            $this->permissions->delete(['id' => $permId]);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function deleteCache()
    {
        $this->cache->removeItem(APP_CACHE_PREFIX.Self::class.':findPermissions');
    }

}
