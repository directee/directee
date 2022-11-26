<?php

namespace Directee\Entrypoint;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WellRESTed\Message\Response;
use WellRESTed\Message\Stream;

class FrontStub implements RequestHandlerInterface
{
    private $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new Response(200))
            ->withHeader('Content-type', 'text/html')
            ->withBody(new Stream($this->text));
        return $response;
    }
}
