<?php

namespace Directee\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class CustomHeaders implements MiddlewareInterface
{
    private $customHeaders;

    public function __construct(array $customHeaders)
    {
        $this->customHeaders = $customHeaders;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        foreach($this->customHeaders as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}
