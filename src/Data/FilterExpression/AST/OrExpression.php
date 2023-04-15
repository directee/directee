<?php

namespace Directee\Data\FilterExpression\AST;

use Directee\Data\FilterExpression\TreeWalker;

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
