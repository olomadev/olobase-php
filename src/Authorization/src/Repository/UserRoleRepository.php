<?php

declare(strict_types=1);

namespace Authorization\Model;

use Exception;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;
use Olobase\DataTable\ColumnFiltersInterface;

class UserRoleRepository implements UserRoleRepositoryInterface
{
    private $conn;
    private $adapter;
    
    public function __construct(
        private TableGatewayInterface $userRoles,
        private ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $userRoles->getAdapter();
        $this->conn    = $this->adapter->getDriver()->getConnection();
    }

    public function assignRole(string $userId, string $roleId): void
    {
        try {
            $this->conn->beginTransaction();
            $this->userRoles->insert(['user_id' => $userId, 'role_id' => $roleId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function unassignRole(string $userId, string $roleId): void
    {
        try {
            $this->conn->beginTransaction();
            $this->userRoles->delete(['user_id' => $userId, 'role_id' => $roleId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function findAllBySelect(string $roleId)
    {
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(['id', 'firstname', 'lastname', 'email']);
        $select->from(['u' => 'users']);
        $select->join(['ur' => 'user_roles'], 'u.id = ur.user_id', [], $select::JOIN_LEFT);
        return $select;
    }

    public function findAllByPaging(array $get): Paginator
    {
        $roleId = $get['roleId'];
        unset($get['roleId']);
        $select = $this->findAllBySelect($roleId);
        $this->columnFilters->clear();
        $this->columnFilters->setColumns(['firstname', 'lastname', 'email']);
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
        $select->where(['ur.role_id' => $roleId]);
        if ($this->columnFilters->orderDataIsNotEmpty()) {
            foreach ($this->columnFilters->getOrderData() as $order) {
                $select->order(new Expression($order));
            }
        }
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $paginatorAdapter = new DbSelect($select, $this->adapter);
        return new Paginator($paginatorAdapter);
    }
}
