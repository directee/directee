<?php

namespace Directee\FilterExpression\AST;

use Directee\FilterExpression\TreeWalker;

class LiteralConstant extends Node
{
    public $literal;

    public function __construct(string $literal)
    {
        $this->literal = $literal;
    }

    public function accept(TreeWalker $walker)
    {
        return $walker->walkLiteralConstant($this);
    }
}
