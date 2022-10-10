<?php

namespace Directee\FilterExpression;

/**
 *
 *
 */
interface TreeWalker
{
    public function walkEqualsExpression(AST\EqualsExpression $node);
    public function walkLessThanExpression(AST\LessThanExpression $node);
    public function walkLessOrEqualExpression(AST\LessOrEqualExpression $node);
    public function walkGreaterThanExpression(AST\GreaterThanExpression $node);
    public function walkGreaterOrEqualExpression(AST\GreaterOrEqualExpression $node);
    public function walkNotEqualExpression(AST\NotEqualExpression $node);

    public function walkInArrayExpression(AST\InArrayExpression $node);
    public function walkNotInArrayExpression(AST\NotInArrayExpression $node);

    public function walkStartsWithExpression(AST\StartsWithExpression $node);
    public function walkEndsWithExpression(AST\EndsWithExpression $node);
    public function walkContainsExpression(AST\ContainsExpression $node);

    public function walkBetweenExpression(AST\BetweenExpression $node);

    public function walkAndExpression(AST\AndExpression $node);
    public function walkOrExpression(AST\OrExpression $node);
    public function walkNotExpression(AST\NotExpression $node);

    public function walkNullValue(AST\NullValue $node);
    public function walkFieldChain(AST\FieldChain $node);
    public function walkLiteralConstant(AST\LiteralConstant $node);
}
