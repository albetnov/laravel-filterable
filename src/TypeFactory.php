<?php

namespace Albet\LaravelFilterable;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Enums\Operators;
use Illuminate\Database\Eloquent\Builder;

class TypeFactory
{
    private ?array $filteredOperator = null;

    public function __construct(readonly FilterableType $filterableType)
    {

    }

    /**
     * @param array<Operators> $allowedOperators
     * @return $this
     */
    public function limit(array $allowedOperators): TypeFactory
    {
        $operatorsValue = collect($allowedOperators)->map(fn(Operators $item) => $item->value);

        if(Operator::is("all", $operatorsValue))
            throw new \InvalidArgumentException("Operator not allowed");

        if(Operator::is($this->filterableType, $operatorsValue))
            throw new \InvalidArgumentException("Operator not supported for type {$this->filterableType->name}");

        $this->filteredOperator = $allowedOperators;
        return $this;
    }

    // TODO: Implement this

    /**
     * @param string $relationship
     * @param (callable(Builder): void)|null $condition
     * @return $this
     */
    public function related(string $relationship, ?callable $condition): TypeFactory
    {
        return $this;
    }

    public function getOperators(): ?array
    {
        return $this->filteredOperator;
    }
}
