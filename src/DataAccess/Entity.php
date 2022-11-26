<?php

namespace Directee\DataAccess;

final class Entity
{
    private string $resource = '';
    private string $keyName = 'id';
    private array $attributes = [];

    public function __construct(EntitySpec $spec)
    {
        $this->resource = $spec->resource;
        $this->keyName = $spec->keyName;
        $this->attributes[$spec->keyName] = null;
        foreach($spec->attributeNames as $name) {
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
        return (bool) $this->attributes[$this->keyName];
    }

    public function getId(): string
    {
        return $this->attributes[$this->keyName] ?? '';
    }

    public function setId($id)
    {
        $this->attributes[$this->keyName] = $id;
    }

    public function getAttribute($name)
    {
        return $this->attributes[$name];
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function asArray(): array
    {
        return $this->attributes;
    }

    public function fromArray($data): void
    {
        foreach($this->attributes as $nm => $vl) {
            $this->attributes[$nm] = $data[$nm] ?? $vl;
        }
    }
}

