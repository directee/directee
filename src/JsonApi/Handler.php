<?php

namespace Directee\JsonApi;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tobyz\JsonApiServer\JsonApi as JsonApiServer;
use Tobyz\JsonApiServer\Schema\Type;
use Directee\Interfaces\Data\DataRepository;

class Handler implements RequestHandlerInterface
{
    private DataRepository $repository;
    private string $apiPath;

    public function __construct(DataRepository $repository, string $apiPath)
    {
        $this->repository = $repository;
        $this->apiPath = $apiPath;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $api = new JsonApiServer($this->apiPath);
        $resource = $request->getAttribute('resource');
        $spec = $this->repository->createSpec($resource);
        $api->resourceType($resource, new DataAdapter($this->repository, $resource), function (Type $type) use ($spec) {
            foreach($spec->attributeNames() as $field) {
                $type->attribute($field)->writable()->filterable()->sortable();
            }
            $type->listable();
            $type->creatable();
            $type->updatable();
            $type->deletable();
            $type->limit(1000);
        });
        $response = $api->handle($request);
        return $response;
    }
}
