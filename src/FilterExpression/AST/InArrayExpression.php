<?php

namespace Directee\FilterExpression\AST;

use Directee\FilterExpression\TreeWalker;

class InArrayExpression extends Node
{
    /** @var Node */
    public $field;
    /** @var array */
    public $array;

    public function __construct(Node $field, array $array)
    {
        $this->field = $field;
        $this->array = $array;
    }

    public function accept(TreeWalker $walker)
    {
        return $walker->walkInArrayExpression($this);
    }
}
