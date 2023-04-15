<?php

namespace Directee\Data\FilterExpression\AST;

use Directee\Data\FilterExpression\TreeWalker;

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
