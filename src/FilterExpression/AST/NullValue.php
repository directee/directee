<?php

namespace Directee\FilterExpression\AST;

use Directee\FilterExpression\TreeWalker;

class NullValue extends Node
{
    public function accept(TreeWalker $walker)
    {
        return $walker->walkNullValue($this);
    }
}
