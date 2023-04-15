<?php

namespace Directee\Data\FilterExpression\AST;

use Directee\Data\FilterExpression\TreeWalker;

class NullValue extends Node
{
    public function accept(TreeWalker $walker)
    {
        return $walker->walkNullValue($this);
    }
}
