<?php

namespace Directee\DataAccess;

interface Repository
{
    public function spec(string $resource): EntitySpec;

    public function create(string $resource): Entity;
    public function find(string $resource, string $id): Entity;

    public function query(string $resource, QueryOptions $options): array;
    public function count(string $resource, QueryOptions $options): int;

    public function save(Entity $entity): void;
    public function delete(Entity $entity): void;
}
