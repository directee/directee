<?php

namespace Directee\DataAccess;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Tobyz\JsonApiServer\Exception\ResourceNotFoundException;

class SchemaRepository implements Repository
{
    private AbstractSchemaManager $schema_manager;
    private array $entities = [];
    private array $tables = [];

    public function __construct(Connection $connection)
    {
        $this->schema_manager = $connection->createSchemaManager();
        $this->tables = $this->schema_manager->listTableNames();
        $this->entities['tables'] = EntitySpec::createFromSpec([
            'resource' => 'tables',
            'keyName' => 'id',
            'attributeNames' => [ 'name', 'columns'],
        ]);
    }

    public function spec(string $resource): EntitySpec
    {
        if (\array_key_exists($resource, $this->entities)) {
            return $this->entities[$resource];
        } else {
            throw new ResourceNotFoundException($resource);
        }
    }

    public function create(string $resource): Entity
    {
        $entity = new Entity($this->spec($resource));
        return $entity;
    }

    public function find(string $resource, string $id): Entity
    {
        $entity = $this->create($resource);
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

    public function query(string $resource, QueryOptions $options): array
    {
        $result = [];
        switch ($resource) {
            case 'tables':
                foreach($this->tables as $tab) {
                    $entity = $this->create($resource);
                    $entity->fromArray($this->getTableData($tab));
                    $result[] = $entity;
                }
                break;
        }
        return $result;
    }

    public function count(string $resource, QueryOptions $options): int
    {
        switch ($resource) {
            case 'tables':
                return count($this->tables);
            default:
                return 0;
        }
    }

    public function save(Entity $entity): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function delete(Entity $entity): void
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
        $primaryKeyColumns = $this->schema_manager->listTableDetails($table)->getPrimaryKey()->getColumns();
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
