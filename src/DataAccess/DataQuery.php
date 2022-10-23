<?php

namespace Directee\DataAccess;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Directee\FilterExpression\Parser;
use Directee\DataAccess\SqlExpressionWalker;


/**
 *
 */
class DataQuery
{
    private $resource;
    private $db;
    private $parser;
    private $keyName;

    public function __construct(string $resource, string $primaryKeyName,  Connection $connection, Parser $parser)
    {
        $this->resource = $resource;
        $this->parser = $parser;
        $this->db = $connection;
        $this->keyName = $primaryKeyName;
        $this->attribute = [];
    }

    public function newDataItem(): DataItem
    {
        return new DataItem($this->resource, $this->keyName, $this->db);
    }

    public function find(string $id)
    {
        $row = $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->resource)
            ->where("$this->keyName = :id")
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative()
        ;
        return ($this->newDataItem())->fromArray($row);
    }

    public function query($options)
    {
        $query = $this->db->createQueryBuilder()->from($this->resource);
        if (empty($options)) {
            $query->select('*');
        } else {
            $this->compileOptions($query, $this->checkOptions($options));
        }
        $result = [];
        foreach($query->executeQuery()->fetchAllAssociative() as $row) {
            $result[] = ($this->newDataItem())->fromArray($row);
        }
        return $result;
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

    private function compileOptions(QueryBuilder $query, array $options)
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
                $query->addOrderBy($field, $order);
            }
        }

        if (isset($options['page'])) {
            if (isset($options['page']['offset'])) {
                $query->setFirstResult($options['page']['offset']);
            }
            if (isset($options['page']['limit'])) {
                $query->setMaxResults($options['page']['limit']);
            }
        }

        if (isset($options['filter'])) {
            $this->parser->parse($options['filter']);
            $query->where($this->parser->getSQL(new SqlExpressionWalker($this->db)));
        }
    }
}
