<?php

namespace Directee\WellBackend;

use Directee\Interfaces\Http\HttpFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use WellRESTed\Message\StreamFactory;
use WellRESTed\Message\RequestFactory;
use WellRESTed\Message\ResponseFactory;

final class WellFactory implements HttpFactory
{
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private StreamFactory $streamFactory;

    public function createRequest(string $method, $uri): RequestInterface
    {
        return $this->requestFactory->createRequest($method, $uri);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->responseFactory->createResponse($code, $reasonPhrase);
    }

    public function createStream(string $content = ''): StreamInterface
    {
        return $this->streamFactory->createStream($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return $this->streamFactory->createStreamFromFile($filename, $mode);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return $this->streamFactory->createStreamFromResource($resource);
    }

    public function createJsonResponse($data, int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        return $this->createResponse($code, $reasonPhrase)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->createStream($json))
        ;
    }

    public function createTextResponse($text, int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->createResponse($code, $reasonPhrase)
            ->withHeader('Content-Type', 'text/plain')
            ->withBody($this->createStream($text))
        ;
    }

    public function createHtmlResponse($html, int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->createResponse($code, $reasonPhrase)
            ->withHeader('Content-Type', 'text/html')
            ->withBody($this->createStream($html))
        ;
    }

    public function __construct()
    {
        $this->streamFactory = new StreamFactory();
        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
    }
}
