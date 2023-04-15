<?php

namespace Directee\JsonApi;

use Closure;
use Tobyz\JsonApiServer\Adapter\AdapterInterface;
use Tobyz\JsonApiServer\Context;
use Tobyz\JsonApiServer\Schema\Attribute;
use Tobyz\JsonApiServer\Schema\HasMany;
use Tobyz\JsonApiServer\Schema\HasOne;
use Tobyz\JsonApiServer\Schema\Relationship;
use Directee\Interfaces\Data\DataRepository;
use Directee\Interfaces\Data\DataQuery;
use Directee\Interfaces\Data\DataItem;

class DataAdapter implements AdapterInterface
{
    private DataRepository $repository;
    private string $resource;

    public function __construct(DataRepository $repository, string $resource)
    {
        $this->repository = $repository;
        $this->resource = $resource;
    }

    public function query()
    {
        return $this->repository->createQuery();
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
        $this->asQuery($query)->addFilterExpression(
            $operator_substitute[$operator] . '(' . $this->getAttributeName($attribute) . ", '"  . $value . "')"
        );
    }

    public function filterByRelationship($query, Relationship $relationship, Closure $scope): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function filterByExpression($query, string $expression): void
    {
        $this->asQuery($query)->addFilterExpression($expression);
    }

    public function sparseFieldset($query, $fields): void
    {
        if (is_string($fields)) {
            foreach(explode(',',$fields) as $field) {
                $this->asQuery($query)->addField($field);
            }
        }
    }

    public function sortByAttribute($query, Attribute $attribute, string $direction): void
    {
        $this->asQuery($query)->addSort($this->getAttributeName($attribute), $direction);
    }

    public function paginate($query, int $limit, int $offset): void
    {
        $this->asQuery($query)->addPaginate($offset, $limit);
    }

    public function find($query, string $id)
    {
        return $this->repository->find($this->resource, $id);
    }

    public function get($query): array
    {
        return $this->repository->query($this->resource, $this->asQuery($query));
    }

    public function count($query): int
    {
        return $this->repository->count($this->resource, $this->asQuery($query));
    }

    public function getId($model): string
    {
        return $this->asItem($model)->getId();
    }

    public function getAttribute($model, Attribute $attribute)
    {
        return $this->asItem($model)->getAttribute($this->getAttributeName($attribute));
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
        return $this->repository->createItem($this->resource);
    }

    public function setId($model, string $id): void
    {
        $this->asItem($model)->setId($id);
    }

    public function setAttribute($model, Attribute $attribute, $value): void
    {
        $this->asItem($model)->setAttribute($this->getAttributeName($attribute), $value);
    }

    public function setHasOne($model, HasOne $relationship, $related): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function save($model): void
    {
        $this->repository->save($this->asItem($model));
    }

    public function saveHasMany($model, HasMany $relationship, array $related): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function delete($model): void
    {
        $this->repository->delete($this->asItem($model));
    }

    private function getAttributeName(Attribute $attribute): string
    {
        return $attribute->getProperty() ?: $attribute->getName();
    }

    private function asItem($model): DataItem
    {
        if ($model instanceof DataItem) {
            return $model;
        } else {
            throw new \RuntimeException('Invalid DataItem instance');
        }
    }

    private function asQuery($query): DataQuery
    {
        if ($query instanceof DataQuery) {
            return $query;
        } else {
            throw new \RuntimeException('Invalid DataQuery instance');
        }
    }
}
