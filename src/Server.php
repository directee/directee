<?php

namespace Directee;

/**
 * Directee Server
 */
class Server
{
    public static function run()
    {
        $loader = new \Nette\DI\ContainerLoader(__DIR__ . '/../temp', true);
        $class = $loader->load(function($compiler) {
	        $compiler->loadConfig(__DIR__ . '/platform.neon');
            $compiler->addConfig([
                'parameters' => [
                    'app_root' => __DIR__ . '/../',
                    'app_temp' => __DIR__ . '/../temp',
                ],
            ]);
            $application_config = __DIR__ . '/../config/application.neon';
            if (file_exists($application_config)) {
                $compiler->loadConfig($application_config);
            }
        });
        $container = new $class;

        $jsonapiServer = $container->getService('jsonapi_server');
        $jsonapiServer->add($container->getService('router'));
        $jsonapiServer->respond();
    }
}
