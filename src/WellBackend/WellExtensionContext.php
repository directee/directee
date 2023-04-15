<?php

namespace Directee\WellBackend;

use Directee\Interfaces\ExtensionContext;

final class WellExtensionContext implements ExtensionContext
{
    public const APIPATH = 'apiPath';

    private array $options;

    public function apiPath(): string
    {
        return $this->options[self::APIPATH] ?? '';
    }

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }
}
