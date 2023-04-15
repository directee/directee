<?php

namespace Directee\Data\FilterExpression\AST;

use Directee\Data\FilterExpression\TreeWalker;

class NotInArrayExpression extends Node
{
    public Node $field;
    public $array = [];

    public function __construct(Node $field, array $array)
    {
        $this->field = $field;
        $this->array = $array;
    }

    public function accept(TreeWalker $walker)
    {
        return $walker->walkNotInArrayExpression($this);
    }
}
