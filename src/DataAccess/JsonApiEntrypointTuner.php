<?php

namespace Directee\DataAccess;

use Tobyz\JsonApiServer\JsonApi;
use Tobyz\JsonApiServer\Schema\Type;
use Doctrine\DBAL\Connection;
use Directee\FilterExpression\Parser;

class JsonApiEntrypointTuner
{

    private $db;
    private $schema;
    private $parser;

    public function __construct(Connection $connection, Parser $parser)
    {
        $this->db = $connection;
        $this->schema = $connection->createSchemaManager();
        $this->parser = $parser;
    }

    public function tuneJsonApi(string $resource, JsonApi $api): void
    {
        $primaryKeyName = $this->getPrimaryKeyName($resource);
        $data_query = new DataQuery($resource, $primaryKeyName, $this->db, $this->parser);
        $fieldlist = $this->schema->listTableColumns($resource);
        $api->resourceType($resource, new DataAdapter($data_query), function (Type $type) use ($fieldlist, $primaryKeyName) {
            foreach($fieldlist as $field) {
                if (!($field->getName() == $primaryKeyName)) {
                    $type->attribute($field->getName())->writable()->filterable();
                }
            }
            $type->listable();
            $type->creatable();
            $type->updatable();
            $type->deletable();
        });
    }

    private function getPrimaryKeyName($resource)
    {
        $primaryKeyColumns = $this->schema->listTableDetails($resource)->getPrimaryKey()->getColumns();
        if (count($primaryKeyColumns) == 1) {
            return $primaryKeyColumns[0];
        } else {
            throw new \RuntimeException("Invalid resource $resource");
        }
    }
}
