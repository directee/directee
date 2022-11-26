<?php

namespace Directee\DataAccess;

use Closure;
use Tobyz\JsonApiServer\Adapter\AdapterInterface;
use Tobyz\JsonApiServer\Context;
use Tobyz\JsonApiServer\Schema\Attribute;
use Tobyz\JsonApiServer\Schema\HasMany;
use Tobyz\JsonApiServer\Schema\HasOne;
use Tobyz\JsonApiServer\Schema\Relationship;

/**
 *
 *
 */
class DataAdapter implements AdapterInterface
{
    private Repository $repository;
    private EntitySpec $entitySpec;

    public function __construct(Repository $repository, EntitySpec $specification)
    {
        $this->repository = $repository;
        $this->entitySpec = $specification;
    }

    public function query()
    {
        return new QueryOptions();
    }

    public function filterByIds($query, array $ids): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function filterByAttribute($query, Attribute $attribute, $value, string $operator = '='): void
    {
        $operator_substitute = [
            '=' => 'eq',
            '>' => 'gt',
            '<' => 'lt',
            '>=' => 'gte',
            '<=' => 'lte',
        ];
        $this->asQueryOptions($query)->addFilterExpression(
            $operator_substitute[$operator] . '(' . $this->getAttributeName($attribute) . ", '"  . $value . "')"
        );
    }

    public function filterByRelationship($query, Relationship $relationship, Closure $scope): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function filterByExpression($query, string $expression): void
    {
        $this->asQueryOptions($query)->addFilterExpression($expression);
    }

    public function sparseFieldset($query, $fields): void
    {
        if (is_string($fields)) {
            foreach(explode(',',$fields) as $field) {
                $this->asQueryOptions($query)->addField($field);
            }
        }
    }

    public function sortByAttribute($query, Attribute $attribute, string $direction): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function paginate($query, int $limit, int $offset): void
    {
        $this->asQueryOptions($query)->pageLimit = $limit;
        $this->asQueryOptions($query)->pageOffset = $offset;
    }

    public function find($query, string $id)
    {
        return $this->repository->find($this->entitySpec->resource, $id);
    }

    public function get($query): array
    {
        return $this->repository->query($this->entitySpec->resource, $this->asQueryOptions($query));
    }

    public function count($query): int
    {
        return $this->repository->count($this->entitySpec->resource, $this->asQueryOptions($query));
    }

    public function getId($model): string
    {
        return $this->asEntity($model)->getId();
    }

    public function getAttribute($model, Attribute $attribute)
    {
        return $this->asEntity($model)->getAttribute($this->getAttributeName($attribute));
    }

    public function getHasOne($model, HasOne $relationship, bool $linkageOnly, Context $context)
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function getHasMany($model, HasMany $relationship, bool $linkageOnly, Context $context)
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function represents($model): bool
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function model()
    {
        return new Entity($this->entitySpec);
    }

    public function setId($model, string $id): void
    {
        $this->asEntity($model)->setId($id);
    }

    public function setAttribute($model, Attribute $attribute, $value): void
    {
        $this->asEntity($model)->setAttribute($this->getAttributeName($attribute), $value);
    }

    public function setHasOne($model, HasOne $relationship, $related): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function save($model): void
    {
        $this->repository->save($this->asEntity($model));
    }

    public function saveHasMany($model, HasMany $relationship, array $related): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function delete($model): void
    {
        $this->repository->delete($this->asEntity($model));
    }

    private function getAttributeName(Attribute $attribute): string
    {
        return $attribute->getProperty() ?: $attribute->getName();
    }

    private function asEntity($model): Entity
    {
        if ($model instanceof Entity) {
            return $model;
        } else {
            throw new \RuntimeException('Invalid Entity instance');
        }
    }

    private function asQueryOptions($query): QueryOptions
    {
        if ($query instanceof QueryOptions) {
            return $query;
        } else {
            throw new \RuntimeException('Invalid QueryOptions instance');
        }
    }
}
