<?php
/*
 * Directee JSON:API Backend
 * Copyright (C) 2022-2023  Andrei V. Goryunov <andrei.goryunov@demob.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Directee\WellBackend;

use Psr\Log\LoggerInterface;
use Directee\Interfaces\Core;
use Directee\Interfaces\Http\HttpFactory;
use Directee\Interfaces\Http\HttpRouter;
use Directee\Interfaces\Data\DataRepository;
use Directee\Interfaces\Auth\AuthInfo;
use League\Container\Container;
use League\Container\Argument\Literal;
use Directee\Data\SchemaRepository;
use Directee\Data\DoctrineDataRepository;
use Directee\Data\DoctrineSchemaRepository;

final class Main implements Core
{
    private Container $container;
    private Container $extbin;
    private Container $inbin;

    public function httpFactory(): HttpFactory
    {
        return $this->inbin->get(HttpFactory::class);
    }

    public function logger(): LoggerInterface
    {
        return $this->inbin->get(LoggerInterface::class);
    }

    public function dataRepository(): DataRepository
    {
        return $this->inbin->get(DataRepository::class);
    }

    public function schemaRepository(): DataRepository
    {
        return $this->inbin->get(SchemaRepository::class);
    }

    public function basePath(): string
    {
        return $this->inbin->get('BASEPATH');
    }

    public function authInfo(): AuthInfo
    {
        return new AuthInfo;
    }

    public function get($name)
    {
        return $this->container->get($name);
    }

    public function __construct(string $work_directory)
    {
        $settings = $this->uploadSettings($work_directory);

        $this->inbin = $this->createInbinContainer($settings);
        $this->container = $this->createCoreContainer($settings);
        $this->inbin->delegate($this->container);
        $this->extbin = $this->createExtbinContainer($settings);
    }

    public function run(): void
    {
        $publicRout = new HttpRouter();
        $publicRout->addHandler('GET', "{$this->basePath()}/", \Directee\Entrypoint\FrontStub::class);
        $publicRout->addHandler('*', "{$this->basePath()}/*", $this->extbin->get(\Directee\JsonApi\Extension::class)->apiRouter());

        $server = new WellServer($this->inbin);
        $server->addMiddleware(new \Directee\Middleware\CustomHeaders($this->inbin->get(Env::CUSTOM_HEADERS)));
        $server->addMiddleware($publicRout);
        $server->respond();
    }

    private static function uploadSettings(string $work_directory): array
    {
        $default_values = [
            Env::DATA_URL => 'sqlite://:memory:',
            Env::CUSTOM_HEADERS => [],
            Env::FRONT_STUB => Env::DIRECTEE_MOTTO,
            Env::APP_MODE => Env::MODE_PRODUCTION,
        ];
        $from_global = \is_array($GLOBALS['directee-settings']) ? $GLOBALS['directee-settings'] : [];
        $from_include = \file_exists("{$work_directory}/directee-settings.php") ? include "{$work_directory}/directee-settings.php" : [];
        $from_config = \file_exists(__DIR__ . '../config/directee-settings.php') ? include __DIR__ . '../config/directee-settings.php' : [];
        $result = \array_replace_recursive($default_values, $from_config, $from_include, $from_global);
        return $result;
    }

    private function createInbinContainer(array $settings): Container
    {
        $bin = new Container();
        if ($settings[Env::APP_MODE] !== Env::MODE_PRODUCTION) {
            $bin->addShared(\Whoops\Run::class, function():\Whoops\Run {
                $whoops = new \Whoops\Run;
                $whoops->pushHandler((new \Whoops\Handler\JsonResponseHandler())->setJsonApi(true)->addTraceToOutput(true));
                $whoops->register();
                return $whoops;
            });
        }
        $bin->addShared('BASEPATH', function ():string {
            $path = \dirname($_SERVER['SCRIPT_NAME']);
            return $path == '/' ? '' : $path;
        });
        $bin->addShared(Env::CUSTOM_HEADERS, new Literal\ArrayArgument($settings[Env::CUSTOM_HEADERS]));
        $bin->addShared(\Directee\Interfaces\Pass\EventBus::class, \Directee\WellEvent\Dispatcher::class)->addArgument($bin);
        $bin->addShared(LoggerInterface::class, \Monolog\Logger::class)->addArgument(new Literal\StringArgument('core'));
        $bin->addShared(\Doctrine\DBAL\Connection::class, function() use ($settings) {
            return \Doctrine\DBAL\DriverManager::getConnection(['url' => $settings[Env::DATA_URL]]);
        });
        $bin->addShared(DataRepository::class, DoctrineDataRepository::class)->addArgument(\Doctrine\DBAL\Connection::class);
        $bin->addShared(SchemaRepository::class, DoctrineSchemaRepository::class)->addArgument(\Doctrine\DBAL\Connection::class);
        $bin->addShared(HttpFactory::class, WellFactory::class);
        $bin->add(\Directee\Entrypoint\FrontStub::class)->addArgument(new Literal\StringArgument($settings[Env::FRONT_STUB]));

        return $bin;
    }

    private function createCoreContainer(): Container
    {
        $bin = new Container();
        return $bin;
    }

    private function createExtbinContainer(): Container
    {
        $bin = new Container();
        $bin->defaultToShared();

        $this->initBuiltinExtensionProvider($bin, 'JsonApi', "{$this->basePath()}");

        return $bin;
    }

    private function initBuiltinExtensionProvider(Container $bin, string $extensionName, string $apiPath): ExtensionProvider
    {
        $provider = new ExtensionProvider(
            "Directee\\{$extensionName}\\Extension",
            __DIR__ . "/../{$extensionName}",
            $this,
            new WellExtensionContext([
                WellExtensionContext::APIPATH => $apiPath,
            ])
        );

        $bin->add("Directee\\{$extensionName}\\Extension", $provider);
        $this->container->delegate($provider);

        return $provider;
    }
}
