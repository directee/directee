<?php

namespace Directee\FilterExpression\AST;

use Directee\FilterExpression\TreeWalker;

class NotExpression extends Node
{
    public $expr;

    public function __construct(Node $expr)
    {
        $this->expr = $expr;
    }

    public function accept(TreeWalker $walker)
    {
        return $walker->walkNotExpression($this);
    }
}
