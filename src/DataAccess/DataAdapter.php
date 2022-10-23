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
    private $dataQuery;

    public function __construct(DataQuery $dataQuery)
    {
        $this->dataQuery = $dataQuery;
    }

    public function query()
    {
        return new \ArrayObject(['query' => []],\ArrayObject::ARRAY_AS_PROPS+\ArrayObject::STD_PROP_LIST);
    }

    public function filterByIds($query, array $ids): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function filterByAttribute($query, Attribute $attribute, $value, string $operator = '='): void
    {
        $operator_substitute = [
            '=' => '$eq',
            '>' => '$gt',
            '<' => '$lt',
            '>=' => '$gte',
            '<=' => '$lte',
        ];
        $query->query['filter'][] = $operator_substitute[$operator] . '(' . $this->getAttributeName($attribute) . ", '"  . $value . "')";
    }

    public function filterByRelationship($query, Relationship $relationship, Closure $scope): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function filterByExpression($query, string $expression): void
    {
        $query->query['filter'] = $expression;
    }

    public function sparseFieldset($query, $fields): void
    {
        if (is_string($fields)) {
            $query->query['fields'] = explode(',',$fields);
        }
    }

    public function sortByAttribute($query, Attribute $attribute, string $direction): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function paginate($query, int $limit, int $offset): void
    {
        $query->query['page'] = [
            'offset' => $offset,
            'limit' => $limit,
        ];
    }

    public function find($query, string $id)
    {
        return $this->dataQuery->find($id);
    }

    public function get($query): array
    {
        return $this->dataQuery->query($query->query);
    }

    public function count($query): int
    {
        return 0;
    }

    public function getId($model): string
    {
        return $this->asModel($model)->getId();
    }

    public function getAttribute($model, Attribute $attribute)
    {
        return $this->asModel($model)->getAttribute($this->getAttributeName($attribute));
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
        return $this->dataQuery->newDataItem();
    }

    public function setId($model, string $id): void
    {
        $this->asModel($model)->setId($id);
    }

    public function setAttribute($model, Attribute $attribute, $value): void
    {
        $this->asModel($model)->setAttribute($this->getAttributeName($attribute), $value);
    }

    public function setHasOne($model, HasOne $relationship, $related): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function save($model): void
    {
        $this->asModel($model)->save();
    }

    public function saveHasMany($model, HasMany $relationship, array $related): void
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not implemented');
    }

    public function delete($model): void
    {
        $this->asModel($model)->delete();
    }

    private function getAttributeName(Attribute $attribute): string
    {
        return $attribute->getProperty() ?: $attribute->getName();
    }

    private function asModel($model): DataItem
    {
        if ($model instanceof DataItem) {
            return $model;
        } else {
            throw new \RuntimeException('Invalid model instance');
        }
    }
}
