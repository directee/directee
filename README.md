# Directee JSON:API Backend

The backend is installed on top of any database (MySQL/MariaDB, PostgreSQL, MS SQL, Sqlite) and allows you to query and modify records in all tables in [JSON:API](https://jsonapi.org/)-way.

Get some records from table TABLE
```
http://your-server/TABLE/
```

Get record with id 123 from table TABLE
```
http://your-server/TABLE/123
```

Get list of tables in your DB:
```
http://your-server/information_schema/tables/
```

You can query records follow [JSON:API](https://jsonapi.org/) specification.
```
```

Also, you can use filter expressions to query data.
```
http://your-server/TABLE/?filter=and(eq(lastName,'Smith'),gte(modified,'2021-01-01'))
```

## Install

### Use composer

Grab the code by composer and point your web server root to the folder `public`

### Use phar

Put the file `directee.phar` on your web server root folder and create the file `index.php`
```php
<?php
require "directee.phar";
```

## Configuration

Put the required settings either:
1) to the file `./directee-settings.php` or `../config/directee-settings.php`
```php
<?php // file directee-settings.php
return [
  'data-url' => 'sqlite://path/to/database',
];
```
2) to the global variable `$GLOBALS['directee-settings']` (usually for phar-distribution)
```php
<?php // index.php
$GLOBALS['directee-settings'] = [
  'data-url' => 'sqlite://path/to/DB',
];
require "directee.phar";
```

### data-url
Specify database connection with [Doctrine database URL](https://www.doctrine-project.org/projects/doctrine-dbal/en/current/reference/configuration.html#connecting-using-a-url). Default value: `sqlite://:memory:`.
```php
    'data-url' => 'pdo-mysql://user:secret@localhost:3306/mydb?charset=utf8mb4',
```

### app-mode
Specify application mode, default value: `production`. In non-production mode it will show detailed error messages.
```php
    'app-mode' => 'develop',
```

### front-stub
Specify the text that will be displayed on the root entry point `/`. Default value: the Directee motto.
You can specify, for example, the content of some file, for the server help page, or for the data access frontend (put others files to the web server root folder).
```php
    'front-stub' => \file_get_contents('index.html'),
```

### custom-headers
Specify additional server http headers. Default value: empty.
```php
    'custom-headers' => [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => '*',
        'Access-Control-Allow-Headers' => '*',
    ],
```

## Filter expressions

You can use filter expressions (`?filter=`) to query records.
```
/employees/?filter=and(eq(sex,'male'),lt(age,'18'))
```

Filter expressions are composed using the following functions:
| Operation                       | Function     | Example |
|---------------------------------|--------------|---------|
| Equality                        | eq           | ?filter=eq(lastName,'Smith') |
| Less than                       | lt           | ?filter=lt(age,'25') |
| Less than or equal to           | lte          | ?filter=lte(lastModified,'2001-01-01') |
| Greater than                    | gt           | ?filter=gt(quantity,'500') |
| Greater than or equal to        | gte          | ?filter=gte(percentage,'33.33') |
| Not equal                       | ne           | ?filter=ne(sex,'female') |
| Contains text                   | contains     | ?filter=contains(description,'cooking') |
| Starts with text                | starts_with  | ?filter=starts_with(description,'The') |
| Ends with text                  | ends_with    | ?filter=ends_with(description,'End') |
| Equals on value from set        | in           | ?filter=in(chapter,'Intro','Summary','Conclusion') |
| Not equal on any value from set | nin          | ?filter=nin(answer,'yes','no') |
| Check value in closed interval  | between      | ?filter=between(value,'50','59') |
| Negation                        | not          | ?filter=not(eq(lastName,null)) |
| Conditional logical OR          | or           | ?filter=or(eq(mode,'prod'),lt(date,'2022-06-01')) |
| Conditional logical AND         | and          | ?filter=and(eq(sex,'male'),lt(age,'18')) |

## On the shoulders of giants

* [doctrine/dbal](https://github.com/doctrine/dbal/)
* [tobyz/json-api-server](https://github.com/tobyz/json-api-server)
* [wellrested/wellrested](https://github.com/wellrested/wellrested)

## Roadmap

* work Level two — authorization/authentication for requests
* work level one — db schema specification for requests
* work level zero — query any databases for all its tables in jsonapi-way

## Development

Assembly `directee.phar` with [Box](https://box-project.github.io/box/)
```shell
box compile
```

## Changelog

### 0.0.3 — 2022-12-12
#### Fixed
- many fixes after field testing

### 0.0.2 — 2022-11-26
#### Added
- entrypoint `/information_schema/tables` to retrieve the list of tables
- directee-settings (custom-headers, front-stub, app-mode)

### 0.0.1 — 2022-09-25
#### Added
- work level zero

## License

AGPL-3.0
