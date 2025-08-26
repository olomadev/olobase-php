<?php

declare(strict_types=1);

namespace Modules\Repository;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;
use Modules\Entity\Module;
use Modularity\DataTable\ColumnFiltersInterface;

class ModuleRepository extends AbstractRepository implements ModuleRepositoryInterface
{
    public function __construct(
        TableGatewayInterface $table,
        StorageInterface $cache,
        private ColumnFiltersInterface $columnFilters
    ) {
        parent::__construct($table, $cache);
    }

    public function findAll(): array
    {
        $key = APP_CACHE_PREFIX . self::class . ':findAll';
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }

        $sql    = new Sql($this->table->getAdapter());
        $select = $sql->select('modules')
            ->columns(['id', 'name', 'version', 'is_active'])
            ->where(['is_active' => 1])
            ->order('name ASC');

        $stmt    = $sql->prepareStatementForSqlObject($select);
        $results = $stmt->execute();

        $modules = [];
        foreach ($results as $row) {
            $modules[] = new Module(
                id: $row['id'],
                name: $row['name'],
                version: $row['version'],
                isActive: (bool) $row['is_active']
            );
        }

        $this->cache->setItem($key, $modules);
        return $modules;
    }

    public function findAllByPaging(array $query): Paginator
    {
        $sql    = new Sql($this->table->getAdapter());
        $select = $sql->select('modules')
            ->columns(['id', 'name', 'version', 'is_active']);

        $this->columnFilters->clear();
        $this->columnFilters->setColumns(['name']);
        $this->columnFilters->setData($query);
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

        $adapter = new DbSelect($select, $this->table->getAdapter());
        return new Paginator($adapter);
    }

    public function findById(string $id): ?Module
    {
        $sql    = new Sql($this->table->getAdapter());
        $select = $sql->select('modules')
            ->columns(['id', 'name', 'version', 'is_active'])
            ->where(['id' => $id]);

        $stmt = $sql->prepareStatementForSqlObject($select);
        $row  = $stmt->execute()->current();

        if (! $row) {
            return null;
        }

        return new Module(
            id: $row['id'],
            name: $row['name'],
            version: $row['version'],
            isActive: (int) $row['is_active']
        );
    }

    /** @inheritDoc */
    protected function doCreate(object $entity)
    {
        /** @var Module $entity */
        $this->table->insert([
            'id'        => $entity->id,
            'name'      => $entity->name,
            'version'   => $entity->version,
            'is_active' => $entity->isActive ? 1 : 0,
        ]);
    }

    /** @inheritDoc */
    protected function doUpdate(object $entity)
    {
        /** @var Module $entity */
        $this->table->update([
            'name'      => $entity->name,
            'version'   => $entity->version,
            'is_active' => $entity->isActive ? 1 : 0,
        ], ['id' => $entity->id]);
    }

    /** @inheritDoc */
    protected function doDelete(string $id)
    {
        $this->table->delete(['id' => $id]);
    }

    protected function deleteCache(): void
    {
        $this->cache->removeItem(APP_CACHE_PREFIX . self::class . ':findAll');
    }
}
