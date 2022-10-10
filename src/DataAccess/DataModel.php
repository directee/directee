<?php

namespace Directee\DataAccess;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Directee\FilterExpression\Parser;
use Directee\DataAccess\SqlExpressionWalker;


/**
 *
 *
 */
class DataModel
{
    private $resource;
    private $explorer;
    private $parser;
    private $keyName;
    private $attribute;

    public function __construct(string $resource, Explorer $explorer, Parser $parser)
    {
        $this->resource = $resource;
        $this->parser = $parser;
        $this->explorer = $explorer;
        $this->keyName = $explorer->getStructure()->getPrimaryKey($resource);
        $this->attribute = [];
    }

    public function newInstance(): DataModel
    {
        return new Self($this->resource, $this->explorer, $this->parser);
    }

    public function getKeyName(): string
    {
        return $this->keyName;
    }

    public function hasId(): bool
    {
        return (bool) $this->attribute[$this->getKeyName()];
    }

    public function getId(): string
    {
        return $this->attribute[$this->getKeyName()] ?? '';
    }

    public function setId(string $id)
    {
        $this->attribute[$this->getKeyName()] = $id;
    }

    public function getAttribute(string $name)
    {
        return $this->attribute[$name];
    }

    public function setAttribute(string $name, $value)
    {
        $this->attribute[$name] = $value;
    }

    public function asArray(): array
    {
        return $this->attribute;
    }

    public function fromArray($data)
    {
        $this->attribute = $data;
    }

    public function find(string $id)
    {
        $this->attribute = $this->explorer->table($this->resource)->get($id)->toArray();
    }

    public function query($options)
    {

        $opts = $this->checkOptions($options);
        $query = $this->explorer->table($this->resource);
        $this->compileOptions($query, $opts);

        $result = [];
        foreach($query->fetchAll() as $row) {
            $model = $this->newInstance();
            $model->fromArray($row);
            $result[] = $model;
        }
        return $result;
    }

    public function save()
    {
        $updated_id = $this->getId();
        if ($this->hasId()) {
            $this->explorer->table($this->resource)->get($this->getId())->update($this->attribute);
        } else {
            $new_row = $this->explorer->table($this->resource)->insert($this->attribute);
            $updated_id = $new_row[$this->getId()];
        }
        $this->find($updated_id);
    }

    public function delete()
    {
        if ($this->hasId()) {
            $this->explorer->table($this->resource)->get($this->getId())->delete();
        }
    }

    private function checkOptions($options)
    {
        $result = [];
        if (\is_array($options)) {
            $result = $options;
        } else {
            \parse_str($options, $result);
        }

        if (\is_array($result['filter'])) {
            if (\count($result['filter']) === 1) {
                $result['filter'] = $result['filter'][0];
            } else {
                $result['filter'] = 'and(' . \implode(',', $result['filter']) . ')';
            }
        }

        return $result;
    }

    private function compileOptions(Selection $query, array $options)
    {
        if (isset($options['fields']) && is_array($options['fields'])) {
            foreach($options['fields'] as $field) {
                $query->addSelect($field);
            }
        } else {
            $query->select('*');
        }

        if (isset($options['sort'])) {
            foreach($options['sort'] as $field => $order) {
                $query->order("$field $order");
            }
        }

        if (isset($options['page'])) {
            $query->limit($options['page']['limit'] ?? 20, $options['page']['offset'] ?? 0);
        }

        if (isset($options['filter'])) {
            $this->parser->parse($options['filter']);
            $query->where($this->parser->getSQL(new SqlExpressionWalker($this->explorer->getConnection())));
        }
    }
}
