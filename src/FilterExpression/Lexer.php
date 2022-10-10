<?php

namespace Directee\FilterExpression;

use Doctrine\Common\Lexer\AbstractLexer;

/**
 *
 *
 */
class Lexer extends AbstractLexer
{
    //
    public const T_NONE              = 1;
    public const T_INTEGER           = 2;
    public const T_FLOAT             = 3;
    public const T_STRING            = 4;
    public const T_LPAREN            = 5;
    public const T_RPAREN            = 6;
    public const T_COMMA             = 7;

    //
    public const T_ALIASED_NAME         = 100;
    public const T_FULLY_QUALIFIED_NAME = 101;
    public const T_IDENTIFIER           = 102;

    //
    public const KEYWORDS               = 200;
    public const T_EQ                   = 201;
    public const T_LT                   = 202;
    public const T_LTE                  = 203;
    public const T_GT                   = 204;
    public const T_GTE                  = 205;
    public const T_NE                   = 206;

    public const T_IN                   = 210;
    public const T_NIN                  = 211;

    public const T_STARTS_WITH          = 220;
    public const T_ENDS_WITH            = 221;
    public const T_CONTAINS             = 222;

    public const T_BETWEEN              = 230;

    public const T_AND                  = 240;
    public const T_OR                   = 241;
    public const T_NOT                  = 242;

    public const T_NULL                 = 260;

    protected function getCatchablePatterns()
    {
        return [
            '[a-z_\\\][a-z0-9_]*(?:\\\[a-z_][a-z0-9_]*)*',  // identifier
            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?',   // number
            "'(?:[^']|'')*'",                               // quoted string
        ];
    }

    protected function getNonCatchablePatterns()
    {
        return ['\s+', '(.)'];
    }

    protected function getType(&$value)
    {
        $type = self::T_NONE;

        switch (true) {

            case is_numeric($value):
                if (strpos($value, '.') !== false || stripos($value, 'e') !== false) {
                    return self::T_FLOAT;
                }
                return self::T_INTEGER;

            case $value[0] === "'":
                $value = str_replace("''", "'", substr($value, 1, strlen($value) - 2));
                return self::T_STRING;

            case ctype_alpha($value[0]) || $value[0] === '_':
                $name = 'Directee\FilterExpression\Lexer::T_' . strtoupper($value);
                if (defined($name)) {
                    $type = constant($name);
                    if ($type > 200) {
                        return $type;
                    }
                }
                return self::T_IDENTIFIER;

            case $value === '(':
                return self::T_LPAREN;
            case $value === ')':
                return self::T_RPAREN;
            case $value === ',':
                return self::T_COMMA;

            default:
                ;
        }
        return $type;
    }
}
