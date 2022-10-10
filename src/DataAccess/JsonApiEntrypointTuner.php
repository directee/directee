<?php

namespace Directee\DataAccess;

use Tobyz\JsonApiServer\JsonApi;
use Tobyz\JsonApiServer\Schema\Type;
use Nette\Database\Explorer;
use Directee\FilterExpression\Parser;

class JsonApiEntrypointTuner
{

    private $explorer;
    private $parser;

    public function __construct(Explorer $explorer, Parser $parser)
    {
        $this->explorer = $explorer;
        $this->parser = $parser;
    }

    public function tuneJsonApi(string $resource, JsonApi $api): void
    {
        $primaryKey = $this->explorer->getStructure()->getPrimaryKey($resource);
        if (is_null($primaryKey)) {
            throw new \RuntimeException("Invalid resource $primaryKey");
        }
        $model = new DataModel($resource, $this->explorer, $this->parser);
        $fieldlist = $this->explorer->getStructure()->getColumns($resource);
        $api->resourceType($resource, new DataAdapter($model), function (Type $type) use ($fieldlist) {
            foreach($fieldlist as $field) {
                if (!$field['primary']) {
                    $type->attribute($field['name'])->writable()->filterable();
                }
            }
            $type->listable();
            $type->creatable();
            $type->updatable();
            $type->deletable();
        });
    }
}
