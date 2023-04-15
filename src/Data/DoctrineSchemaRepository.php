<?php

namespace Directee\Data;

use Directee\Interfaces\Data\DataItem;
use Directee\Interfaces\Data\DataQuery;
use Directee\Interfaces\Data\DataSpec;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Tobyz\JsonApiServer\Exception\ResourceNotFoundException;

class DoctrineSchemaRepository implements SchemaRepository
{
    private AbstractSchemaManager $schema_manager;
    private array $entities = [];
    private array $tables = [];

    public function __construct(Connection $connection)
    {
        $this->schema_manager = $connection->createSchemaManager();
        $this->tables = $this->schema_manager->listTableNames();
        $this->entities['tables'] = Spec::createFromSpec([
            'resource' => 'tables',
            'keyName' => 'id',
            'attributeNames' => [ 'name', 'columns'],
        ]);
    }

    public function createSpec(string $resource): DataSpec
    {
        if (\array_key_exists($resource, $this->entities)) {
            return $this->entities[$resource];
        } else {
            throw new ResourceNotFoundException($resource);
        }
    }

    public function createItem(string $resource): DataItem
    {
        $item = new Item($this->createSpec($resource));
        return $item;
    }

    public function createQuery(): DataQuery
    {
        return new Query();
    }

    public function find(string $resource, string $id): DataItem
    {
        $entity = $this->createItem($resource);
        $row = [];
        switch ($resource) {
            case 'tables':
                $row = $this->getTableData($id);
                break;
            default:
                $row = [];
                break;
        }
        $entity->fromArray($row);
        return $entity;
    }

    public function query(string $resource, DataQuery $query): array
    {
        $result = [];
        switch ($resource) {
            case 'tables':
                foreach($this->tables as $tab) {
                    $entity = $this->createItem($resource);
                    $entity->fromArray($this->getTableData($tab));
                    $result[] = $entity;
                }
                break;
        }
        return $result;
    }

    public function count(string $resource, DataQuery $query): int
    {
        switch ($resource) {
            case 'tables':
                return count($this->tables);
            default:
                return 0;
        }
    }

    public function save(DataItem $item): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function delete(DataItem $item): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    private function getTableData(string $table): array
    {
        if (\in_array($table, $this->tables)) {
            return [
                'id' => $table,
                'name' => $table,
                'columns' => $this->getFields($table),
            ];
        } else {
            return [
                'id' => null,
                'name' => null,
                'columns' => [],
            ];
        }
    }

    private function getFields(string $table): array
    {
        $result = [];
        $primaryKeyColumns = $this->schema_manager->introspectTable($table)->getPrimaryKey()->getColumns();
        foreach($this->schema_manager->listTableColumns($table) as $column) {
            if (in_array($column->getName(), $primaryKeyColumns)) {
                continue;
            } else {
                $result[] = [
                    'name' => $column->getName(),
                    'type' => $column->getType()->getName(),
                ];
            }
        }
        return $result;
    }
}
