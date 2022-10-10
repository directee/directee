<?php

namespace Directee\FilterExpression\AST;

use Directee\FilterExpression\TreeWalker;

class BetweenExpression extends Node
{
    public $field;
    public $left;
    public $right;

    public function __construct(Node $field, Node $left, Node $right)
    {
        $this->field = $field;
        $this->left = $left;
        $this->right = $right;
    }

    public function accept(TreeWalker $walker)
    {
        return $walker->walkBetweenExpression($this);
    }
}
