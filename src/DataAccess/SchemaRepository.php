<?php

namespace Directee\DataAccess;

use Doctrine\DBAL\Connection;
use Tobyz\JsonApiServer\Exception\ResourceNotFoundException;

class SchemaRepository implements Repository
{
    private array $entities = [];
    private array $tables = [];

    public function __construct(Connection $connection)
    {
        $schema_manager = $connection->createSchemaManager();
        $this->tables = $schema_manager->listTableNames();
        $this->entities['tables'] = EntitySpec::createFromSpec([
            'resource' => 'tables',
            'keyName' => 'id',
            'attributeNames' => [ 'name', ],
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
                $row = [
                    'id' => \in_array($id, $this->tables) ? $id : null,
                    'name' => \in_array($id, $this->tables) ? $id : null,
                ];
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
                    $entity->fromArray([
                        'id' => $tab,
                        'name' => $tab,
                    ]);
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
}
