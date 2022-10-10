# Directee

Directee is the common JSONAPI backend.

The backend is installed on top of any database (MySQL/MariaDB, PostgreSQL, MS SQL, Sqlite) and allows you to query and modify records in all tables in JSONAPI-way.

Also, you can use filter expressions to query data.

## How to use

### 1) Install

Use composer

### 2) Tune

Fill the config file `config/application.neon` with database connection information

### 3) Run

From within php built-in server, for example

## Filter expressions

You can use filter expressions (?filter=) to query records.

## On the shoulders of giants

* tobyz/json-api-server
* nette/database
* basemaster/wellrested

## Roadmap

* work Level two — authorization/authentication for requests
* work level one — db schema specification for requests
* work level zero — query any databases for all its tables in jsonapi-way

## Changelog

### 0.0.1 — 2022-09-25
#### Added
- work level zero

## License

MIT
