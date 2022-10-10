<?php

namespace Directee\Entrypoint;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WellRESTed\Message\Response;
use WellRESTed\Message\Stream;

class FrontStub implements RequestHandlerInterface
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new Response(200))
            ->withHeader('Content-type', 'text/plain')
            ->withBody(new Stream($this->name));
        return $response;
    }
}
