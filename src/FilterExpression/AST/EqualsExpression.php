<?php

namespace Directee\FilterExpression\AST;

use Directee\FilterExpression\TreeWalker;

class EqualsExpression extends Node
{
    public $left_hand;
    public $right_hand;

    public function __construct(Node $left_hand, Node $right_hand)
    {
        $this->left_hand = $left_hand;
        $this->right_hand = $right_hand;
    }

    public function accept(TreeWalker $walker)
    {
        return $walker->walkEqualsExpression($this);
    }
}
