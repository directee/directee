<?php

namespace Directee\Data;

use Doctrine\DBAL\Connection;
use Directee\Data\FilterExpression\TreeWalker;
use Directee\Data\FilterExpression\AST\EqualsExpression;
use Directee\Data\FilterExpression\AST\LessThanExpression;
use Directee\Data\FilterExpression\AST\LessOrEqualExpression;
use Directee\Data\FilterExpression\AST\GreaterThanExpression;
use Directee\Data\FilterExpression\AST\GreaterOrEqualExpression;
use Directee\Data\FilterExpression\AST\NotEqualExpression;
use Directee\Data\FilterExpression\AST\InArrayExpression;
use Directee\Data\FilterExpression\AST\NotInArrayExpression;
use Directee\Data\FilterExpression\AST\StartsWithExpression;
use Directee\Data\FilterExpression\AST\EndsWithExpression;
use Directee\Data\FilterExpression\AST\ContainsExpression;
use Directee\Data\FilterExpression\AST\BetweenExpression;
use Directee\Data\FilterExpression\AST\AndExpression;
use Directee\Data\FilterExpression\AST\NotExpression;
use Directee\Data\FilterExpression\AST\OrExpression;
use Directee\Data\FilterExpression\AST\NullValue;
use Directee\Data\FilterExpression\AST\FieldChain;
use Directee\Data\FilterExpression\AST\LiteralConstant;

class SqlExpressionWalker implements TreeWalker
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    private function expr($field, $op, $value)
    {
        return "{$field} {$op} {$value}";
    }

    public function walkEqualsExpression(EqualsExpression $node)
    {
        return $this->expr($node->left_hand->accept($this), '=', $node->right_hand->accept($this));
    }

    public function walkLessThanExpression(LessThanExpression $node)
    {
        return $this->expr($node->left_hand->accept($this), '<', $node->right_hand->accept($this));
    }

    public function walkLessOrEqualExpression(LessOrEqualExpression $node)
    {
        return $this->expr($node->left_hand->accept($this), '<=', $node->right_hand->accept($this));
    }

    public function walkGreaterThanExpression(GreaterThanExpression $node)
    {
        return $this->expr($node->left_hand->accept($this), '>', $node->right_hand->accept($this));
    }

    public function walkGreaterOrEqualExpression(GreaterOrEqualExpression $node)
    {
        return $this->expr($node->left_hand->accept($this), '>=', $node->right_hand->accept($this));
    }

    public function walkNotEqualExpression(NotEqualExpression $node)
    {
        return $this->expr($node->left_hand->accept($this), '!=', $node->right_hand->accept($this));
    }

    public function walkInArrayExpression(InArrayExpression $node)
    {
        $array = \array_map(function($item) { return $item->accept($this); }, $node->array);
        return $this->expr($node->field->accept($this), 'IN', '(' . implode(', ', $array) . ')' );
    }

    public function walkNotInArrayExpression(NotInArrayExpression $node)
    {
        $array = \array_map(function($item) { return $item->accept($this); }, $node->array);
        return $this->expr($node->field->accept($this), 'NOT IN', '(' . implode(', ', $array) . ')' );
    }

    public function walkStartsWithExpression(StartsWithExpression $node)
    {
        $expr = $this->connection->quote($node->value . '%');
        return $this->expr($node->field->accept($this), 'LIKE', $expr);
    }

    public function walkEndsWithExpression(EndsWithExpression $node)
    {
        $expr = $this->connection->quote('%' . $node->value);
        return $this->expr($node->field->accept($this), 'LIKE', $expr);
    }

    public function walkContainsExpression(ContainsExpression $node)
    {
        $expr = $this->connection->quote('%' . $node->value . '%');
        return $this->expr($node->field->accept($this), 'LIKE', $expr);
    }

    public function walkBetweenExpression(BetweenExpression $node)
    {
        $field = $node->field->accept($this);
        $left  = $node->left->accept($this);
        $right = $node->right->accept($this);
        return "{$field} BETWEEN {$left} AND {$right}";
    }

    public function walkAndExpression(AndExpression $node)
    {
        $items = \array_map(function($item) { return $item->accept($this); }, $node->items);
        if (\count($items) === 1) {
            return $items[0];
        } else {
            return '(' . \implode(') AND (', $items) . ')';
        }
    }

    public function walkOrExpression(OrExpression $node)
    {
        $items = \array_map(function($item) { return $item->accept($this); }, $node->items);
        if (\count($items) === 1) {
            return $items[0];
        } else {
            return '(' . \implode(') OR (', $items) . ')';
        }
    }

    public function walkNotExpression(NotExpression $node)
    {
        $result = $node->expr->accept($this);
        return "NOT ({$result})";
    }

    public function walkNullValue(NullValue $node)
    {
        return 'NULL';
    }

    public function walkFieldChain(FieldChain $node)
    {
        return "`{$node->name}`";
    }

    public function walkLiteralConstant(LiteralConstant $node)
    {
        return $this->connection->quote($node->literal);
    }
}
