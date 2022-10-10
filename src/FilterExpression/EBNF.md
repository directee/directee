## EBNF

```
filterExpression:
    notExpression
    | logicalExpression
    | comparisonExpression
    | matchTextExpression
    | arrayExpression
    | betweenExpression
    ;

notExpression:
    'not' LPAREN filterExpression RPAREN;

logicalExpression:
    ( 'and' | 'or' ) LPAREN filterExpression ( COMMA filterExpression )* RPAREN;

comparisonExpression:
    ( 'eq' | 'gt' | 'gte' | 'lt' | 'lte' | 'ne' ) LPAREN fieldChain ) COMMA ( literalConstant | 'null' | fieldChain ) RPAREN;

matchTextExpression:
    ( 'contains' | 'starts_with' | 'ends_with' ) LPAREN fieldChain COMMA literalConstant RPAREN;

arrayExpression:
    ( 'in' | 'nin' ) LPAREN fieldChain COMMA literalConstant ( COMMA literalConstant )+ RPAREN;

betweenExpression:
    'between' LPAREN fieldChain COMMA literalConstant COMMA literalConstant RPAREN;

fieldChain:
    FIELD ( '.' FIELD )*;

literalConstant:
    ESCAPED_TEXT | INTEGER | FLOAT;

LPAREN: '(';
RPAREN: ')';
COMMA: ',';

fragment OUTER_FIELD_CHARACTER: [A-Za-z0-9];
fragment INNER_FIELD_CHARACTER: [A-Za-z0-9_-];
FIELD: OUTER_FIELD_CHARACTER ( INNER_FIELD_CHARACTER* OUTER_FIELD_CHARACTER )?;

ESCAPED_TEXT: '\'' ( ~['] | '\'\'' )* '\'' ;

fragment DIGIT_CHARACTER: [0-9];
INTEGER: DIGIT_CHARACTER ( DIGIT_CHARACTER )*;
FLOAT: INTEGER ( '.' INTEGER )? ('e' ('+' | '-')? INTEGER)?;

LINE_BREAKS: [\r\n]+ -> skip;
```
