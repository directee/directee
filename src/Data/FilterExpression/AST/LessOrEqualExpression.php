<?php

namespace Directee\Data\FilterExpression\AST;

use Directee\Data\FilterExpression\TreeWalker;

class LessOrEqualExpression extends Node
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
        return $walker->walkLessOrEqualExpression($this);
    }
}
