<?php

namespace Directee\Data\FilterExpression\AST;

use Directee\Data\FilterExpression\TreeWalker;

class FieldChain extends Node
{
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function accept(TreeWalker $walker)
    {
        return $walker->walkFieldChain($this);
    }
}
