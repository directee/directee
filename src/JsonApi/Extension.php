<?php

namespace Directee\JsonApi;

use Directee\Interfaces\Core;
use Directee\Interfaces\Extension as ExtensionInterface;
use Directee\Interfaces\ExtensionContext;
use League\Container\Container;
use League\Container\Argument\Literal;

final class Extension implements ExtensionInterface
{
    protected Container $container;

    public function __construct()
    {
        $this->container = new Container();
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function has($id): bool
    {
        return $this->container->has($id);
    }

    public function connect(Core $core, ExtensionContext $context): void
    {
        $this->container->addShared(HttpFactory::class, $core->httpFactory());
        $this->container->addShared(DataRepository::class, $core->dataRepository());
        $this->container->addShared(SchemaRepository::class, $core->schemaRepository());
        $this->container->addShared('Directee\JsonApi\jsonapiSchemaHandler', Handler::class)->addArguments([
            SchemaRepository::class,
            new Literal\StringArgument("{$context->apiPath()}/information_schema"),
        ]);
        $this->container->addShared('Directee\JsonApi\jsonapiDataHandler', Handler::class)->addArguments([
            DataRepository::class,
            new Literal\StringArgument("{$context->apiPath()}"),
        ]);
    }
}
