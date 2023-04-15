<?php

namespace Directee\WellBackend;

use Directee\Interfaces\Http\HttpRouter;
use Psr\Container\ContainerInterface;
use WellRESTed\Server;
use WellRESTed\Routing\Router;

class WellServer extends Server
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    public function addMiddleware($middleware): void
    {
        if ($middleware instanceof HttpRouter) {
            $this->add($this->compileRouter($middleware));
        } elseif (is_string($middleware)) {
            $this->add(function () use ($middleware) { return $this->container->get($middleware); });
        } else {
            $this->add($middleware);
        }
    }

    private function compileRouter(HttpRouter $router): Router
    {
        $realRouter = $this->createRouter();
        $base = $router->basePath();
        foreach($router->handlers() as $item) {
            if ($item[2] instanceof HttpRouter) {
                $realRouter->register($item[0], $base . $item[1], $this->compileRouter($item[2]));
            } elseif (is_string($item[2])) {
                $id = $item[2];
                $realRouter->register($item[0], $base . $item[1], function () use ($id) { return $this->container->get($id); });
            } else {
                $realRouter->register($item[0], $base . $item[1], $item[2]);
            }
        };
        foreach($router->middlewares() as $item) {
            if ($item instanceof HttpRouter) {
                $realRouter->add($this->compileRouter($item));
            } else {
                if (is_string($item)) {
                    $realRouter->add(function () use ($item) { return $this->container->get($item); });
                } else {
                    $realRouter->add($item);
                }
            }
        };
        $realRouter->continueOnNotFound();
        return $realRouter;
    }
}
