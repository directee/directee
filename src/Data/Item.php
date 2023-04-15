<?php

namespace Directee\Data;

use Directee\Interfaces\Data\DataItem;
use Directee\Interfaces\Data\DataSpec;

class Item implements DataItem
{
    private string $resource = '';
    private string $keyName = 'id';
    private $keyValue = '';
    private array $attributes = [];

    public function __construct(DataSpec $spec)
    {
        $this->resource = $spec->resource();
        $this->keyName = $spec->keyName();
        foreach($spec->attributeNames() as $name) {
            $this->attributes[$name] = null;
        }
    }

    public function resource(): string
    {
        return $this->resource;
    }

    public function keyName(): string
    {
        return $this->keyName;
    }

    public function hasId(): bool
    {
        return (bool) $this->keyValue;
    }

    public function getId(): string
    {
        return $this->keyValue;
    }

    public function setId($id): void
    {
        $this->keyValue = $id;
    }

    public function getAttribute($name)
    {
        return $this->attributes[$name];
    }

    public function setAttribute(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function attributes(): array
    {
        return $this->attributes;
    }

    public function asArray(): array
    {
        $result = $this->attributes();
        $result[$this->keyName()] = $this->getId();
        return $result;
    }

    public function fromArray($data): void
    {
        $this->keyValue = $data[$this->keyName()];
        foreach($this->attributes as $nm => $vl) {
            $this->attributes[$nm] = $data[$nm] ?? $vl;
        }
    }
}
