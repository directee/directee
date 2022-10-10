<?php

namespace Directee\FilterExpression;

/**
 *
 *
 */
class Parser
{
    private $lexer;
    private $ast;

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
    }

    public function parse($string): void
    {
        $this->lexer->setInput($string);
        $this->lexer->moveNext();
        $this->ast = $this->parseFilterExpression();
    }

    public function getSQL(TreeWalker $walker): string
    {
        return $this->ast->accept($walker);
    }

    public function getAST()
    {
        return $this->ast;
    }

    private function parseFilterExpression(): AST\Node
    {
        switch($this->lexer->lookahead['type']) {
            case Lexer::T_EQ:
            case Lexer::T_LT:
            case Lexer::T_LTE:
            case Lexer::T_GT:
            case Lexer::T_GTE:
            case Lexer::T_NE:
                return $this->parseComparisionExpression($this->lexer->lookahead['type']);
            case Lexer::T_IN:
            case Lexer:: T_NIN:
                return $this->parseArrayExpression($this->lexer->lookahead['type']);
            case Lexer::T_STARTS_WITH:
            case Lexer::T_ENDS_WITH:
            case Lexer::T_CONTAINS:
                return $this->parseMatchTextExpression($this->lexer->lookahead['type']);
            case Lexer::T_BETWEEN:
                return $this->parseBetweenExpression($this->lexer->lookahead['type']);
            case Lexer::T_NOT:
                return $this->parseNotExpression();
            case Lexer::T_AND:
            case Lexer::T_OR:
                return $this->parseLogicalExpression($this->lexer->lookahead['type']);
            default:
                $this->syntaxError();
        }
    }

    private function parseComparisionExpression($token): AST\Node
    {
        $left_hand = null;
        $right_hand = null;

        $this->match($token);
        $this->match(Lexer::T_LPAREN);

        if ($this->lexer->lookahead['type'] == Lexer::T_IDENTIFIER) {
            $this->lexer->moveNext();
            $left_hand = new AST\FieldChain($this->lexer->token['value']);
        } else {
            $this->syntaxError('fieldChain');
        }

        $this->match(Lexer::T_COMMA);

        switch (true) {
            case $this->lexer->isNextToken(Lexer::T_INTEGER):
            case $this->lexer->isNextToken(Lexer::T_FLOAT):
            case $this->lexer->isNextToken(Lexer::T_STRING):
                $this->lexer->moveNext();
                $right_hand = new AST\LiteralConstant($this->lexer->token['value']);
                break;
            case $this->lexer->isNextToken(Lexer::T_NULL):
                $this->lexer->moveNext();
                $right_hand = new AST\NullValue();
                break;
            case $this->lexer->isNextToken(Lexer::T_IDENTIFIER):
                $this->lexer->moveNext();
                $right_hand = new AST\FieldChain($this->lexer->token['value']);
                break;
            default:
                $this->syntaxError('literalConstant');
                break;
        }

        $this->match(Lexer::T_RPAREN);

        switch($token) {
            case Lexer::T_EQ:
                return new AST\EqualsExpression($left_hand, $right_hand);
            case Lexer::T_GT:
                return new AST\GreaterThanExpression($left_hand, $right_hand);
            case Lexer::T_GTE:
                return new AST\GreaterOrEqualExpression($left_hand, $right_hand);
            case Lexer::T_LT:
                return new AST\LessThanExpression($left_hand, $right_hand);
            case Lexer::T_LTE:
                return new AST\LessOrEqualExpression($left_hand, $right_hand);
            case Lexer::T_NE:
                return new AST\NotEqualExpression($left_hand, $right_hand);
            default:
                $this->syntaxError('comparision function');
        }
    }

    private function parseMatchTextExpression($token): AST\Node
    {
        $left_hand = null;
        $right_hand = null;

        $this->match($token);
        $this->match(Lexer::T_LPAREN);

        if ($this->lexer->lookahead['type'] == Lexer::T_IDENTIFIER) {
            $this->lexer->moveNext();
            $left_hand = new AST\FieldChain($this->lexer->token['value']);
        } else {
            $this->syntaxError('fieldChain');
        }

        $this->match(Lexer::T_COMMA);

        switch (true) {
            case $this->lexer->isNextToken(Lexer::T_STRING):
                $this->lexer->moveNext();
                $right_hand = $this->lexer->token['value'];
                break;
            default:
                $this->syntaxError('literalConstant');
                break;
        }

        $this->match(Lexer::T_RPAREN);

        switch ($token) {
            case Lexer::T_STARTS_WITH:
                return new AST\StartsWithExpression($left_hand, $right_hand);
            case Lexer::T_CONTAINS:
                return new AST\ContainsExpression($left_hand, $right_hand);
            case Lexer::T_ENDS_WITH:
                return new AST\EndsWithExpression($left_hand, $right_hand);
            default:
                $this->syntaxError('text match function');
        }
    }

    private function parseArrayExpression($token): AST\Node
    {
        $field = null;
        $array = null;

        $this->match($token);
        $this->match(Lexer::T_LPAREN);

        if ($this->lexer->lookahead['type'] == Lexer::T_IDENTIFIER) {
            $this->lexer->moveNext();
            $field = new AST\FieldChain($this->lexer->token['value']);
        } else {
            $this->syntaxError('fieldChain');
        }

        $this->match(Lexer::T_COMMA);

        while (true) {
            switch (true) {
                case $this->lexer->isNextToken(Lexer::T_INTEGER):
                case $this->lexer->isNextToken(Lexer::T_FLOAT):
                case $this->lexer->isNextToken(Lexer::T_STRING):
                    $this->lexer->moveNext();
                    $array[] = new AST\LiteralConstant($this->lexer->token['value']);
                    break;
                case $this->lexer->isNextToken(Lexer::T_NULL):
                    $this->lexer->moveNext();
                    $array[] = new AST\NullValue();
                    break;
                default:
                    $this->syntaxError('literalConstant');
                    break;
            }

            if ($this->lexer->isNextToken(Lexer::T_COMMA)) {
                $this->lexer->moveNext();
            } else {
                break;
            }
        }

        $this->match(Lexer::T_RPAREN);

        switch($token) {
            case Lexer::T_IN:
                return new AST\InArrayExpression($field, $array);
            case Lexer::T_NIN:
                return new AST\NotInArrayExpression($field, $array);
            default:
                $this->syntaxError('array function');
        }
    }

    private function parseBetweenExpression($token): AST\Node
    {
        $field = null;
        $left = null;
        $right = null;

        $this->match($token);
        $this->match(Lexer::T_LPAREN);

        if ($this->lexer->lookahead['type'] == Lexer::T_IDENTIFIER) {
            $this->lexer->moveNext();
            $field = new AST\FieldChain($this->lexer->token['value']);
        } else {
            $this->syntaxError('fieldChain');
        }

        $this->match(Lexer::T_COMMA);

        switch (true) {
            case $this->lexer->isNextToken(Lexer::T_INTEGER):
            case $this->lexer->isNextToken(Lexer::T_FLOAT):
            case $this->lexer->isNextToken(Lexer::T_STRING):
                $this->lexer->moveNext();
                $left = new AST\LiteralConstant($this->lexer->token['value']);
                break;
            default:
                $this->syntaxError('literalConstant');
                break;
        }

        $this->match(Lexer::T_COMMA);

        switch (true) {
            case $this->lexer->isNextToken(Lexer::T_INTEGER):
            case $this->lexer->isNextToken(Lexer::T_FLOAT):
            case $this->lexer->isNextToken(Lexer::T_STRING):
                $this->lexer->moveNext();
                $right = new AST\LiteralConstant($this->lexer->token['value']);
                break;
            default:
                $this->syntaxError('literalConstant');
                break;
        }

        $this->match(Lexer::T_RPAREN);

        return new AST\BetweenExpression($field, $left, $right);
    }

    private function parseLogicalExpression($token): AST\Node
    {
        $items = [];

        $this->match($token);
        $this->match(Lexer::T_LPAREN);

        while (true) {
            $items[] = $this->parseFilterExpression();

            if ($this->lexer->isNextToken(Lexer::T_COMMA)) {
                $this->lexer->moveNext();
            } else {
                break;
            }
        }

        $this->match(Lexer::T_RPAREN);

        switch ($token) {
            case Lexer::T_OR:
                return new AST\OrExpression($items);
            case Lexer::T_AND:
                return new AST\AndExpression($items);
            default:
                $this->syntaxError('logic function');
                break;
        }
    }

    private function parseNotExpression(): AST\NotExpression
    {
        $this->match(Lexer::T_NOT);
        $this->match(Lexer::T_LPAREN);
        $expr = $this->parseFilterExpression();
        $this->match(Lexer::T_RPAREN);

        return new AST\NotExpression($expr);
    }

    private function match($token)
    {
        $lookaheadType = $this->lexer->lookahead['type'];

        if ($lookaheadType === $token) {
            $this->lexer->moveNext();
            return;
        }

        $this->syntaxError($this->lexer->getLiteral($token));
    }

    private function syntaxError($expected = '', $token = null)
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        $tokenPos = $token['position'] ?? '-1';

        $message  = sprintf('line 0, col %d: Error: ', $tokenPos);
        $message .= $expected !== '' ? sprintf('Expected %s, got ', $expected) : 'Unexpected ';
        $message .= $this->lexer->lookahead === null ? 'end of string.' : sprintf("'%s'", $token['value']);

        throw new \RuntimeException($message);
    }
}
