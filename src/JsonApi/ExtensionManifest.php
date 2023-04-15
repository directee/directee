<?php
require_once __DIR__ . '/../../vendor/autoload.php';

return (new \Directee\Interfaces\ExtensionManifest())
    ->setName('JsonApi')
    ->setDescription('A JSONAPI-compliant server')
    ->addApiHandler('GET', '/information_schema/{resource}', 'Directee\JsonApi\jsonapiSchemaHandler')
    ->addApiHandler('GET', '/information_schema/{resource}/', 'Directee\JsonApi\jsonapiSchemaHandler')
    ->addApiHandler('GET', '/information_schema/{resource}/{id}', 'Directee\JsonApi\jsonapiSchemaHandler')
    ->addApiHandler('GET', '/information_schema/{resource}/{id}/', 'Directee\JsonApi\jsonapiSchemaHandler')
    ->addApiHandler('GET,POST', '/{resource}', 'Directee\JsonApi\jsonapiDataHandler')
    ->addApiHandler('GET,POST', '/{resource}/', 'Directee\JsonApi\jsonapiDataHandler')
    ->addApiHandler('GET,PATCH,DELETE', '/{resource}/{id}', 'Directee\JsonApi\jsonapiDataHandler')
    ->addApiHandler('GET,PATCH,DELETE', '/{resource}/{id}/', 'Directee\JsonApi\jsonapiDataHandler')
;
