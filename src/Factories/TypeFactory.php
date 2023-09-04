<?php

namespace Albet\LaravelFilterable\Factories;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Enums\Operators;
use Albet\LaravelFilterable\Operator;
use Illuminate\Database\Eloquent\Builder;

class TypeFactory
{
    private ?array $filteredOperator = null;

    private ?array $related = null;

    public function __construct(private readonly ?FilterableType $filterableType = null)
    {

    }

    public function __call(string $method, array $arguments)
    {
        return match ($method) {
            'getOperators' => $this->filteredOperator,
            'getRelated' => $this->related,
            'getType' => $this->filterableType,
            default => throw new \BadMethodCallException("Method {$method} does not exist")
        };
    }

    /**
     * @param  array<Operators>  $allowedOperators
     * @return $this
     */
    public function limit(array $allowedOperators): TypeFactory
    {
        $operatorsValue = Operators::toValues($allowedOperators);

        if ($this->filterableType && ! Operator::is($this->filterableType, $operatorsValue->toArray())) {
            throw new \InvalidArgumentException("Operator not supported for type {$this->filterableType->name}");
        }

        $this->filteredOperator = $allowedOperators;

        return $this;
    }

    /**
     * @param  (callable(Builder): void)|null  $condition
     * @return $this
     */
    public function related(string $relationship, callable $condition = null): TypeFactory
    {
        $this->related = [
            'relationship' => $relationship,
            'condition' => $condition,
        ];

        return $this;
    }
}
