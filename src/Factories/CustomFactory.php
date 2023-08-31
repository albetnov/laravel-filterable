<?php

namespace Albet\LaravelFilterable\Factories;

class CustomFactory
{
    public function __construct(private readonly ?TypeFactory $factory = null)
    {

    }

    public function __call(string $method, array $arguments)
    {
        if($method === "get") {
            return $this->factory?->getOperators();
        }
    }
}
