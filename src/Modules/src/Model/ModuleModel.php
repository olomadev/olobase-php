<?php
declare(strict_types=1);

namespace Modules\Model;

use Exception;
use Olobase\Mezzio\ColumnFiltersInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class ModuleModel implements ModuleModelInterface
{
    private $conn;
    private $adapter;

    /**
     * Constructor
     * 
     * @param TableGatewayInterface $modules object
     * @param StorageInterface $cache object
     * @param ColumnFilters object
     */
    public function __construct(
        private TableGatewayInterface $modules,
        private StorageInterface $cache,
        private ColumnFiltersInterface $columnFilters
    )
    {        
        $this->adapter = $modules->getAdapter();
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    public function findAll(): array
    {
        $key = CACHE_ROOT_KEY.Self::class.':'. __FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        try {
            $sql = new Sql($this->adapter);
            $select = $sql->select();
            $select->from(['m' => 'modules']);
            $select->columns([
                    'id',
                    'name',
                    'version',
                ]);

            if (getenv("APP_ENV") != "local") {
                $select->where(['isActive' => true]);    
            }
            $select->order('name ASC');

            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = $statement->execute();
            $results = iterator_to_array($resultSet, false);

            // Her eleman için uiName ekle
            foreach ($results as &$result) {
                // Kebap case formatında uiName oluştur
                $result['uiName'] = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $result['name']));
            }

            if (!empty($results)) {
                $this->cache->setItem($key, $results);
            }

            return $results;
        } catch (\Throwable $e) {
            return [];
        }
    }
    
    public function findAllBySelect()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id',
            'name',
            'version',
            'isActive'
        ]);
        $select->from(['m' => 'modules']);
        return $select;
    }

    public function findAllByPaging(array $get) : Paginator
    {
        $select = $this->findAllBySelect();
        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'name',
        ]);
        $this->columnFilters->setData($get);
        $this->columnFilters->setSelect($select);

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
                $select->order(new Expression($order));
            }
        }
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $paginatorAdapter = new DbSelect(
            $select,
            $this->adapter
        );
        return new Paginator($paginatorAdapter);
    }

    public function findOneById(string $moduleId)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id',
            'name',
            'version',
            'isActive'
        ]);
        $select->from(['m' => 'modules']);
        $select->where(['m.id' => $moduleId]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();
        return $row;
    }

    public function create(array $data) : void
    {
        try {
            $this->conn->beginTransaction();
            $this->modules->insert($data['modules']);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data) : void
    {
        unset($data['modules']['id']);
        try {
            $this->conn->beginTransaction();
            $this->modules->update($data['modules'], ['id' => $data['id']]);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $moduleId) : void
    {
        try {
            $this->conn->beginTransaction();
            $this->modules->delete(['id' => $moduleId]);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function deleteCache() : void
    {
        $this->cache->removeItem(CACHE_ROOT_KEY.Self::class.':findAll');
    }    

}
