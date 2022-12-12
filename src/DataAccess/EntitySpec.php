<?php

namespace Directee\DataAccess;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Tobyz\JsonApiServer\Schema\Type;
use Tobyz\JsonApiServer\Exception\NotImplementedException;

final class EntitySpec
{
    public string $resource;
    public string $keyName;
    public array $attributeNames = [];

    private function __construct()
    {
    }

    public static function createFromSchema(AbstractSchemaManager $schema, string $resource): EntitySpec
    {
        $result = new Self();
        $table = $schema->listTableDetails($resource);

        $result->resource = $resource;

        $primaryKeyColumns = $table->getPrimaryKey()->getColumns();
        if (count($primaryKeyColumns) == 1) {
            $result->keyName = $primaryKeyColumns[0];
        } else {
            throw new NotImplementedException("Complex primary key is not supported. Resource: $resource");
        }

        foreach($table->getColumns() as $column) {
            if ($result->keyName == $column->getName()) {
                continue;
            }
            $result->attributeNames[] = $column->getName();
        }

        return $result;
    }

    public static function createFromSpec(array $spec): EntitySpec
    {
        $result = new Self();
        $result->resource = $spec['resource'];
        $result->keyName = $spec['keyName'];
        $result->attributeNames = $spec['attributeNames'];
        return $result;
    }

    public function tuneResourceType(Type $type): void
    {
        foreach($this->attributeNames as $field) {
            $type->attribute($field)->writable()->filterable()->sortable();
        }
        $type->listable();
        $type->creatable();
        $type->updatable();
        $type->deletable();
        $type->limit(1000);
    }
}
