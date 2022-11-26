<?php

namespace Directee;

use Pimple\Container;

/**
 * Directee Server
 *
 * @property-read \WellRESTed\Server $jsonapi_server
 */
class Server
{
    public static function run(string $work_directory)
    {
        $server = new Self(self::uploadSettings($work_directory));
        $server->jsonapi_server->respond();
    }

    public const PRODUCTION_MODE = 'production';

    private Container $container;

    private const APP_MODE = 'app_mode';
    private const DB_CONNECTION = 'db_connection';
    private const DATA_REPOSITORY = 'data_repository';
    private const SCHEMA_REPOSITORY = 'schema_repository';
    private const JSONAPI_SERVER = 'jsonapi_server';
    private const ENTRYPOINT_FRONT_STUB = 'entrypoint_front_stub';
    private const ENTRYPOINT_JSONAPI_DATA = 'entrypoint_jsonapi_data';
    private const ENTRYPOINT_JSONAPI_SCHEMA = 'entrypoint_jsonapi_schema';

    private const DIRECTEE_MOTTO = 'Directee is the common JSONAPI backend';
    private const INFORMATION_SCHEMA = '/information_schema';

    private const CONF_DATA_URL = 'data-url';
    private const CONF_FRONT_STUB = 'front-stub';
    private const CONF_CUSTOM_HEADERS = 'custom-headers';
    private const CONF_WORK_DIRECTORY = 'work-directory';
    private const CONF_APP_MODE = 'app-mode';

    private function __construct(array $settings)
    {
        $this->container = new Container([
            self::APP_MODE => $settings[self::CONF_APP_MODE],
            self::DB_CONNECTION => function() use ($settings) {
                $connectionParams = [
                    'url' => $settings[self::CONF_DATA_URL],
                ];
                return \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
            },
            self::DATA_REPOSITORY => function($container) {
                return new \Directee\DataAccess\DataRepository($container[self::DB_CONNECTION]);
            },
            self::SCHEMA_REPOSITORY => function($container) {
                return new \Directee\DataAccess\SchemaRepository($container[self::DB_CONNECTION]);
            },
            self::JSONAPI_SERVER => function() {
                return new \WellRESTed\Server();
            },
        ]);

        $container = $this->container;

        $this->container[self::ENTRYPOINT_FRONT_STUB] = $this->container->protect(function() use ($settings) {
            $text = $settings[self::CONF_FRONT_STUB];
            return new \Directee\Entrypoint\FrontStub($text);
        });
        $this->container[self::ENTRYPOINT_JSONAPI_DATA] = $this->container->protect(function() use ($container) {
            return new \Directee\Entrypoint\JsonApi($container[self::DATA_REPOSITORY], '', $container[self::APP_MODE]);
        });
        $this->container[self::ENTRYPOINT_JSONAPI_SCHEMA] = $this->container->protect(function() use ($container) {
            return new \Directee\Entrypoint\JsonApi($container[self::SCHEMA_REPOSITORY], self::INFORMATION_SCHEMA, $container[self::APP_MODE]);
        });

        $this->container->extend(self::JSONAPI_SERVER, function(\WellRESTed\Server $server, $container) use ($settings) {
            $router = $server->createRouter();
            $router
                ->register('GET', '/', $container[self::ENTRYPOINT_FRONT_STUB])
                ->register('GET', self::INFORMATION_SCHEMA .'/{resource}', $container[self::ENTRYPOINT_JSONAPI_SCHEMA])
                ->register('GET', self::INFORMATION_SCHEMA . '/{resource}/', $container[self::ENTRYPOINT_JSONAPI_SCHEMA])
                ->register('GET', self::INFORMATION_SCHEMA . '/{resource}/{id}', $container[self::ENTRYPOINT_JSONAPI_SCHEMA])
                ->register('GET', self::INFORMATION_SCHEMA . '/{resource}/{id}/', $container[self::ENTRYPOINT_JSONAPI_SCHEMA])
                ->register('GET,POST', '/{resource}', $container[self::ENTRYPOINT_JSONAPI_DATA])
                ->register('GET,POST', '/{resource}/', $container[self::ENTRYPOINT_JSONAPI_DATA])
                ->register('GET,PATCH,DELETE', '/{resource}/{id}', $container[self::ENTRYPOINT_JSONAPI_DATA])
                ->register('GET,PATCH,DELETE', '/{resource}/{id}/', $container[self::ENTRYPOINT_JSONAPI_DATA])
            ;
            $server->add(new \Directee\Middleware\CustomHeaders($settings[self::CONF_CUSTOM_HEADERS]));
            $server->add($router);
            return $server;
        });
    }

    public function get(string $name)
    {
        return $this->container[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->container[$name]);
    }

    public function __get(string $name)
	{
		return $this->get($name);
	}

    private static function uploadSettings(string $work_directory): array
    {
        $default_values = [
            self::CONF_DATA_URL => 'sqlite://:memory:',
            self::CONF_CUSTOM_HEADERS => [],
            self::CONF_WORK_DIRECTORY => '',
            self::CONF_FRONT_STUB => self::DIRECTEE_MOTTO,
            self::CONF_APP_MODE => self::PRODUCTION_MODE,
        ];
        $from_global = \is_array($GLOBALS['directee-settings']) ? $GLOBALS['directee-settings'] : [];
        $from_include = \file_exists("${work_directory}/directee-settings.php") ? include "${work_directory}/directee-settings.php" : [];
        $from_config = \file_exists(__DIR__ . '../config/directee-settings.php') ? include __DIR__ . '../config/directee-settings.php' : [];
        $result = \array_replace_recursive($default_values, $from_config, $from_include, $from_global);
        $result[self::CONF_WORK_DIRECTORY] = $work_directory;
        return $result;
    }
}
