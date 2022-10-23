<?php

namespace Directee;

use \Pimple\Container;

/**
 * Directee Server
 *
 * This is core container with these entries:
 * @property-read \WellRESTed\Server $json_api_server
 */
class Server
{
    public static function run(string $work_dir)
    {
        $settings = include "$work_dir/directee-settings.php" ?? [ 'data-url' => 'sqlite://:memory:' ];
        $server = new Self($settings);
        $server->json_api_server->respond();
    }

    /** @var \Pimple\Container */
    private $container;

    private const app_name = 'app_name';
    private const jsonapi_server = 'json_api_server';
    private const jsonapi_handler = 'jsonapi_handler';
    private const jsonapi_tuner = 'jsonapi_tuner';
    private const db_connection = 'db_connection';
    private const front_stub = 'entrypoint_front_stub';
    private const filter_lexer = 'filter_lexer';
    private const filter_parser = 'filter_parser';

    private function __construct(array $settings)
    {
        $this->container = new Container([
            self::app_name => 'Directee is the common JSONAPI backend',
            self::db_connection => function() use ($settings) {
                $connectionParams = [
                    'url' => $settings['data-url'],
                ];
                return \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
            },
            self::filter_lexer => function() {
                return new \Directee\FilterExpression\Lexer();
            },
            self::filter_parser => function($container) {
                return new \Directee\FilterExpression\Parser($container[self::filter_lexer]);
            },
            self::jsonapi_server => function() {
                return new \WellRESTed\Server();
            },
            self::jsonapi_tuner => function($container) {
                return new \Directee\DataAccess\JsonApiEntrypointTuner($container[self::db_connection], $container[self::filter_parser]);
            },
            self::jsonapi_handler => function($container) {
                return new \Directee\Entrypoint\JsonApi($container[self::jsonapi_tuner]);
            },
            self::front_stub => function($container) {
                return new \Directee\Entrypoint\FrontStub($container[self::app_name]);
            },
        ]);
        $this->container->extend(self::jsonapi_server, function(\WellRESTed\Server $server, $container){
            $router = $server->createRouter();
            $router
                ->register('GET', '/', $container[self::front_stub])
                ->register('GET,POST', '/{resource}', $container[self::jsonapi_handler])
                ->register('GET,POST', '/{resource}/', $container[self::jsonapi_handler])
                ->register('GET,PATCH,DELETE', '/{resource}/{id}', $container[self::jsonapi_handler])
                ->register('GET,PATCH,DELETE', '/{resource}/{id}/', $container[self::jsonapi_handler])
            ;
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
}
