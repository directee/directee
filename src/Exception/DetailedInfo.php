<?php

namespace Directee\Exception;

use RuntimeException;
use Tobyz\JsonApiServer\ErrorProviderInterface;
use JsonApiPhp\JsonApi\Error;

class DetailedInfo extends RuntimeException implements ErrorProviderInterface
{
    private \Throwable $failure;

    public function __construct(\Throwable $e)
    {
        $this->failure = $e;
    }

    public function getJsonApiErrors(): array
    {
        $detailed = new Error(
            new Error\Title('Detailed Error Info'),
            new Error\Status($this->getJsonApiStatus()),
            new Error\Detail($this->failure->getMessage())
        );
        if ($this->failure instanceof ErrorProviderInterface) {
            return \array_merge($this->failure->getJsonApiErrors(), $detailed);
        } else {
            return $detailed;
        }
    }

    public function getJsonApiStatus(): string
    {
        return ($this->failure instanceof ErrorProviderInterface) ? $this->failure->getJsonApiStatus() : '500';
    }
}
