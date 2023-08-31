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

    public function __construct(readonly ?FilterableType $filterableType = null)
    {

    }

    /**
     * @param  array<Operators>  $allowedOperators
     * @return $this
     */
    public function limit(array $allowedOperators): TypeFactory
    {
        $operatorsValue = collect($allowedOperators)->map(fn (Operators $item) => $item->value);

        if (Operator::is('all', $operatorsValue)) {
            throw new \InvalidArgumentException('Operator not allowed');
        }

        if ($this->filterableType && Operator::is($this->filterableType, $operatorsValue)) {
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

    public function getOperators(): ?array
    {
        return $this->filteredOperator;
    }

    public function getRelated(): ?array
    {
        return $this->related;
    }
}
