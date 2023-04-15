<?php

namespace Directee\Data;

use Directee\Interfaces\Data\DataRepository;
use Directee\Interfaces\Data\DataSpec;
use Directee\Interfaces\Data\DataItem;
use Directee\Interfaces\Data\DataQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Directee\Data\FilterExpression\Parser;
use Directee\Data\FilterExpression\Lexer;
use Tobyz\JsonApiServer\Exception\ResourceNotFoundException;
use Tobyz\JsonApiServer\Exception\NotImplementedException;

class DoctrineDataRepository implements DataRepository
{
    private Connection $connection;
    private AbstractSchemaManager $schema_manager;
    private array $entities = [];
    private array $tableNames = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->schema_manager = $connection->createSchemaManager();
        $this->tableNames = $this->schema_manager->listTableNames();
    }

    public function createSpec(string $resource): DataSpec
    {
        if (! \array_key_exists($resource, $this->entities)) {
            if (\in_array($resource, $this->tableNames)) {
                $this->entities[$resource] = $this->createDataSpec($resource);
            } else {
                throw new ResourceNotFoundException($resource);
            }
        }
        return $this->entities[$resource];
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
        $item = $this->createItem($resource);
        $row = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($item->resource())
            ->where($item->keyName() . ' = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative()
        ;
        $item->fromArray($row);
        return $item;
    }

    public function query(string $resource, DataQuery $query): array
    {
        $spec = $this->createSpec($resource);
        $builder = $this->connection->createQueryBuilder()->from($spec->resource());
        $this->compileQuery($builder, $query);
        $result = [];
        foreach($builder->executeQuery()->fetchAllAssociative() as $row) {
            $item = $this->createItem($resource);
            $item->fromArray($row);
            $result[] = $item;
        }
        return $result;
    }

    public function count(string $resource, DataQuery $query): int
    {
        $count_query = $this->createQuery();
        $count_query->addField('COUNT(*)');
        $count_query->addFilterExpression($query->filterExpression());
        $spec = $this->createSpec($resource);
        $builder = $this->connection->createQueryBuilder()->from($spec->resource());
        $this->compileQuery($builder, $count_query);
        $result = (int) $builder->executeQuery()->fetchOne();
        return $result;
    }

    public function save(DataItem $item): void
    {
        $updated_id = $item->getId();
        if ($item->hasId()) {
            $query = $this->connection->createQueryBuilder()->update($item->resource());
            $query->where($item->keyName() . ' = :id')->setParameter('id', $updated_id);
            foreach($item->attributes() as $nm => $vl) {
                $query->set($nm,":$nm")->setParameter($nm,$vl);
            }
            $query->executeStatement();
        } else {
            $query = $this->connection->createQueryBuilder()->insert($item->resource());
            foreach($item->attributes() as $nm => $vl) {
                $query->setValue($nm,":$nm")->setParameter($nm,$vl);
            }
            $resu = $query->executeQuery()->fetchAllAssociative();
            $updated_id = $this->connection->lastInsertId($item->resource());
        }
        $row = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($item->resource())
            ->where($item->keyName() . ' = :id')->setParameter('id', $updated_id)
            ->executeQuery()
            ->fetchAssociative()
        ;
        $item->fromArray($row);
    }

    public function delete(DataItem $item): void
    {
        if ($item->hasId()) {
            $query = $this->connection->createQueryBuilder()->delete($item->resource());
            $query->where($item->keyName() . ' = :id')->setParameter('id', $item->getId());
            $query->executeStatement();
        }
    }

    private function compileQuery(QueryBuilder $builder, DataQuery $query)
    {
        if (empty($query->fields())) {
            $builder->select('*');
        } else {
            foreach($query->fields() as $field) {
                $builder->addSelect($field);
            }
        }

        foreach($query->sort() as $field => $order) {
            $builder->addOrderBy($field, $order);
        }

        $builder->setFirstResult($query->pageOffset());
        $builder->setMaxResults($query->pageLimit());

        if ($expr = $query->filterExpression()) {
            $parser = new Parser(new Lexer);
            $parser->parse($expr);
            $builder->where($parser->getSQL(new SqlExpressionWalker($this->connection)));
        }
    }

    private function createDataSpec(string $resource): DataSpec
    {
        $spec = [];
        $table = $this->schema_manager->introspectTable($resource);

        $spec['resource'] = $resource;

        $primaryKeyColumns = $table->getPrimaryKey()->getColumns();
        if (count($primaryKeyColumns) == 1) {
            $spec['keyName'] = $primaryKeyColumns[0];
        } else {
            throw new NotImplementedException("Complex primary key is not supported. Resource: $resource");
        }

        foreach($table->getColumns() as $column) {
            if ($spec['keyName'] == $column->getName()) {
                continue;
            }
            $spec['attributeNames'][] = $column->getName();
        }

        return Spec::createFromSpec($spec);
    }
}
