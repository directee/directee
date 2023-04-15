<?php

namespace Directee\Data;

use Directee\Interfaces\Data\DataSpec;

final class Spec implements DataSpec
{
    private string $resource;
    private string $keyName;
    private array $attributeNames = [];

    public function resource(): string
    {
        return $this->resource;
    }

    public function keyName(): string
    {
        return $this->keyName;
    }

    public function attributeNames(): array
    {
        return $this->attributeNames;
    }

    private function __construct()
    {
    }

    public static function createFromSpec(array $spec): DataSpec
    {
        $result = new Self();
        $result->resource = $spec['resource'];
        $result->keyName = $spec['keyName'];
        $result->attributeNames = $spec['attributeNames'];
        return $result;
    }
}
