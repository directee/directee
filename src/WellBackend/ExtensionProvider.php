<?php

namespace Directee\WellBackend;

use Directee\Interfaces\Core;
use Directee\Interfaces\Extension;
use Directee\Interfaces\ExtensionContext;
use Directee\Interfaces\ExtensionManifest;
use Directee\Interfaces\Http\HttpRouter;
use Psr\Container\ContainerInterface;

use function is_null;
use function in_array;
use function array_keys;

final class ExtensionProvider implements ContainerInterface
{
    private Core $core;
    private ExtensionContext $context;
    private string $class;
    private string $folder;
    private ExtensionManifest $manifest;
    private ?Extension $extension = null;
    private array $provides;

    public function __construct(string $class, string $folder, Core $core, ExtensionContext $context)
    {
        $this->core = $core;
        $this->context = $context;
        $this->class = $class;
        $this->folder = $folder;
        $this->manifest = require "{$folder}/ExtensionManifest.php";
        $this->manifest->setApiBasePath($context->apiPath());
        $this->provides = $this->parseProvides($this->manifest);
    }

    public function name(): string
    {
        return $this->manifest->name();
    }

    public function apiRouter(): HttpRouter
    {
        return $this->manifest->apiRouter();
    }

    public function get(string $id)
    {
        return $this->extension()->get($id);
    }

    public function has(string $id): bool
    {
        return in_array($id, $this->provides);
    }

    private function extension(): Extension
    {
        if (is_null($this->extension)) {
            require "{$this->folder}/Extension.php";
            $this->extension = new $this->class;
            $this->extension->connect($this->core, $this->context);
        }
        return $this->extension;
    }

    private function parseProvides(ExtensionManifest $manifest): array
    {
        $provides = [];
        foreach($manifest->apiRouter()->handlers() as $item) {
            if (is_string($item[2])) {
                $provides[$item[2]] = $item[2];
            }
        }
        foreach($manifest->apiRouter()->middlewares() as $item) {
            if (is_string($item)) {
                $provides[$item] = $item;
            }
        }
        foreach($manifest->services() as $item) {
            if (is_string($item)) {
                $provides[$item] = $item;
            }
        }

        return array_keys($provides);
    }
}
