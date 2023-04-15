<?php

namespace Directee\Data;

use Directee\Interfaces\Data\DataQuery;

final class Query implements DataQuery
{
    private array $filterSet = [];
    private array $sort = [];
    private array $fields = [];
    private int $pageOffset = 0;
    private int $pageLimit = 20;

    public function addFilterExpression(string $expr): void
    {
        $this->filterSet[] = $expr;
    }

    public function filterExpression(): string
    {
        if (empty($this->filterSet)) {
            return '';
        }
        if (\count($this->filterSet) === 1) {
            return $this->filterSet[0];
        } else {
            return 'and(' . \implode(',', $this->filterSet) . ')';
        }
    }

    public function addSort(string $field, string $direction): void
    {
        $this->sort[$field] = $direction;
    }

    public function sort(): array
    {
        return $this->sort;
    }

    public function addField(string $field): void
    {
        $this->fields[] = $field;
    }

    public function fields(): array
    {
        return $this->fields;
    }

    public function addPaginate(int $offset, int $limit): void
    {
        $this->pageOffset = $offset;
        $this->pageLimit = $limit;
    }

    public function pageOffset(): int
    {
        return $this->pageOffset;
    }

    public function pageLimit(): int
    {
        return $this->pageLimit;
    }
}
