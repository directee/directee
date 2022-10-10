<?php

namespace Directee\Entrypoint;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tobyz\JsonApiServer\JsonApi as JsonApiServer;
use Directee\DataAccess\JsonApiEntrypointTuner;
use Nette\Database\Explorer;

/**
 *  Обработчик запросов к ресурсам по JSONAPI
 */
class JsonApi implements RequestHandlerInterface
{

    private $jsonapi_tuner;
    private $explorer;

    public function __construct(JsonApiEntrypointTuner $jsonapi_tuner,  Explorer $explorer)
    {
        $this->jsonapi_tuner = $jsonapi_tuner;
        $this->explorer = $explorer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $api = new JsonApiServer('/');
        $resource = $request->getAttribute('resource');
        try {
            $this->jsonapi_tuner->tuneJsonApi($resource, $api);
            $response = $api->handle($request);
        } catch (\Exception | \Error $e) {
            $response = $api->error($e);
        }
        return $response;
    }
}
