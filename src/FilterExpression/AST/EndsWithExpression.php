<?php

namespace Directee\FilterExpression\AST;

use Directee\FilterExpression\TreeWalker;

class EndsWithExpression extends Node
{
    public $field;
    public $value;

    public function __construct(Node $field, string $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function accept(TreeWalker $walker)
    {
        return $walker->walkEndsWithExpression($this);
    }
}
