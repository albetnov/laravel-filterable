<?php

namespace Albet\LaravelFilterable\Factories;

class CustomFactory
{
    public function __construct(private readonly ?TypeFactory $factory = null)
    {

    }

    public function __call(string $method, array $arguments)
    {
        if ($method === 'get') {
            /** @phpstan-ignore-next-line */
            return $this->factory?->getOperators();
        }
    }
}
