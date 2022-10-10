<?php

namespace Directee\FilterExpression\AST;

use Directee\FilterExpression\TreeWalker;
use const PHP_EOL;
use function get_class;
use function get_object_vars;
use function is_array;
use function is_object;
use function str_repeat;
use function var_export;

/**
 *
 */
abstract class Node
{
    public function accept(TreeWalker $walker)
    {
        throw new \RuntimeException('Accept for node ' . get_class($this) . ' is not supported');
    }

    public function __toString()
    {
        return $this->dump($this);
    }

    private function dump($obj)
    {
        static $ident = 0;

        $str = '';

        if ($obj instanceof Node) {
            $str  .= get_class($obj) . '(' . PHP_EOL;
            $props = get_object_vars($obj);

            foreach ($props as $name => $prop) {
                $ident += 4;
                $str   .= str_repeat(' ', $ident) . '"' . $name . '": '
                      . $this->dump($prop) . ',' . PHP_EOL;
                $ident -= 4;
            }

            $str .= str_repeat(' ', $ident) . ')';
        } elseif (is_array($obj)) {
            $ident += 4;
            $str   .= 'array(';
            $some   = false;

            foreach ($obj as $k => $v) {
                $str .= PHP_EOL . str_repeat(' ', $ident) . '"'
                      . $k . '" => ' . $this->dump($v) . ',';
                $some = true;
            }

            $ident -= 4;
            $str   .= ($some ? PHP_EOL . str_repeat(' ', $ident) : '') . ')';
        } elseif (is_object($obj)) {
            $str .= 'instanceof(' . get_class($obj) . ')';
        } else {
            $str .= var_export($obj, true);
        }

        return $str;
    }
}
