<?php

namespace Directee\Entrypoint;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tobyz\JsonApiServer\JsonApi as JsonApiServer;
use Directee\DataAccess\Repository;
use Directee\DataAccess\DataAdapter;
use Directee\Server;
use Tobyz\JsonApiServer\ErrorProviderInterface;
use Directee\Exception\DetailedInfo;

/**
 *
 */
class JsonApi implements RequestHandlerInterface
{
    private Repository $repository;
    private string $appMode;
    private string $basePath;

    public function __construct(Repository $repository, string $basePath, string $appMode)
    {
        $this->repository = $repository;
        $this->basePath = $basePath;
        $this->appMode = $appMode;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $api = new JsonApiServer($this->basePath);
        $resource = $request->getAttribute('resource');
        try {
            $spec = $this->repository->spec($resource);
            $api->resourceType($resource, new DataAdapter($this->repository, $spec), [$spec, 'tuneResourceType']);
            $response = $api->handle($request);
        } catch (\Exception | \Error $e) {
            $response = $api->error($this->appMode == Server::PRODUCTION_MODE ? $e : new DetailedInfo($e));
        }
        return $response;
    }
}
