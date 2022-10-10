<?php

namespace Directee\FilterExpression\AST;

use Directee\FilterExpression\TreeWalker;

class OrExpression extends Node
{
    public $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function accept(TreeWalker $walker)
    {
        return $walker->walkOrExpression($this);
    }
}
