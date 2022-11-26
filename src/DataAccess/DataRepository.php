<?php

namespace Directee\DataAccess;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Directee\FilterExpression\Parser;
use Directee\FilterExpression\Lexer;

class DataRepository implements Repository
{
    private Connection $connection;
    private AbstractSchemaManager $schema_manager;
    private array $entities = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->schema_manager = $connection->createSchemaManager();
    }

    public function spec(string $resource): EntitySpec
    {
        if (! \array_key_exists($resource, $this->entities)) {
            $this->entities[$resource] = $this->createEntitySpec($resource);
        }
        return $this->entities[$resource];
    }

    public function create(string $resource): Entity
    {
        $entity = new Entity($this->spec($resource));
        return $entity;
    }

    public function find(string $resource, string $id): Entity
    {
        $entity = $this->create($resource);
        $row = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($entity->resource())
            ->where($entity->keyName() . ' = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative()
        ;
        $entity->fromArray($row);
        return $entity;
    }

    public function query(string $resource, QueryOptions $options): array
    {
        $spec = $this->spec($resource);
        $query = $this->connection->createQueryBuilder()->from($spec->resource);
        $this->compileOptions($query, $options);
        $result = [];
        foreach($query->executeQuery()->fetchAllAssociative() as $row) {
            $entity = $this->create($resource);
            $entity->fromArray($row);
            $result[] = $entity;
        }
        return $result;
    }

    public function count(string $resource, QueryOptions $options): int
    {
        $count_options = new QueryOptions();
        $count_options->addField('COUNT(*)');
        $count_options->addFilterExpression($options->filterExpression());
        $spec = $this->spec($resource);
        $query = $this->connection->createQueryBuilder()->from($spec->resource);
        $this->compileOptions($query, $count_options);
        $result = (int) $query->executeQuery()->fetchOne();
        return $result;
    }

    public function save(Entity $entity): void
    {
        $updated_id = $entity->getId();
        if ($entity->hasId()) {
            $query = $this->connection->createQueryBuilder()->update($entity->resource());
            $query->where($entity->keyName() . ' = :id')->setParameter('id', $updated_id);
            foreach($entity->asArray() as $nm => $vl) {
                $prm = ":$nm";
                $query->set($nm,$prm)->setParameter($prm,$vl);
            }
            $query->executeStatement();
        } else {
            $query = $this->connection->createQueryBuilder()->insert($entity->resource());
            foreach($entity->asArray() as $nm => $vl) {
                $prm = ":$nm";
                $query->setValue($nm,$prm)->setParameter($prm,$vl);
            }
            $resu = $query->executeQuery()->fetchAllAssociative();
            $updated_id = $this->connection->lastInsertId($entity->resource());
        }
        $row = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($entity->resource())
            ->where($entity->keyName() . ' = :id')->setParameter('id', $updated_id)
            ->executeQuery()
            ->fetchAssociative()
        ;
        $entity->fromArray($row);
    }

    public function delete(Entity $entity): void
    {
        if ($entity->hasId()) {
            $query = $this->connection->createQueryBuilder()->delete($entity->resource());
            $query->where($entity->keyName() . ' = :id')->setParameter('id', $entity->getId());
            $query->executeStatement();
        }
    }

    private function compileOptions(QueryBuilder $query, QueryOptions $options)
    {
        if (empty($options->fields())) {
            $query->select('*');
        } else {
            foreach($options->fields() as $field) {
                $query->addSelect($field);
            }
        }

        foreach($options->sort() as $field => $order) {
            $query->addOrderBy($field, $order);
        }

        $query->setFirstResult($options->pageOffset);
        $query->setMaxResults($options->pageLimit);

        if ($expr = $options->filterExpression()) {
            $parser = new Parser(new Lexer);
            $parser->parse($expr);
            $query->where($parser->getSQL(new SqlExpressionWalker($this->connection)));
        }
    }

    private function createEntitySpec(string $resource): EntitySpec
    {
        switch ($resource) {
            default:
                return EntitySpec::createFromSchema($this->schema_manager, $resource);
        }
    }
}
