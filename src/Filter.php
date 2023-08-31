<?php

namespace Albet\LaravelFilterable;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Exceptions\OperatorNotExist;
use Albet\LaravelFilterable\Exceptions\OperatorNotValid;
use Albet\LaravelFilterable\Factories\CustomFactory;
use Albet\LaravelFilterable\Factories\TypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Filter
{
    private \Closure $parent;

    public function __construct(
        private readonly Builder $builder,
        private readonly ?array $filters,
        private readonly ?array $rows
    ) {
    }

    /**
     * @throws OperatorNotExist
     */
    private function matchCustomOperators(array $customOperators, string $operator): void
    {
        if (! in_array($operator, $customOperators)) {
            throw new OperatorNotExist($operator);
        }
    }

    /**
     * @throws OperatorNotValid
     */
    private function handle(FilterableType $type, array $filter, Builder $builder): void
    {
        if (! Operator::is($type, $filter['operator'])) {
            throw new OperatorNotValid($filter['operator'], $type->name);
        }

        $handler = new Handler($builder, $filter['field'], $filter['operator'], $filter['value']);

        match ($type) {
            FilterableType::TEXT => $handler->handleText(),
            FilterableType::NUMBER => $handler->handleNumber(),
            FilterableType::DATE => $handler->handleDate(),
            FilterableType::BOOLEAN => $handler->handleBoolean(),
        };
    }

    /**
     * @throws OperatorNotExist
     */
    private function handleTypeFactory(TypeFactory $typeFactory, array $filter): void
    {
        if ($typeFactory->getOperators()) {
            $this->matchCustomOperators($typeFactory->getOperators(), $filter['operator']);
        }

        $relationship = $typeFactory->getRelated();
        if ($relationship) {
            $this->builder->whereHas($relationship['relationship'], function (Builder $query) use ($relationship, $typeFactory, $filter) {
                if ($relationship['condition']) {
                    $relationship['condition']($query);
                }
                $this->handle($typeFactory->filterableType, $filter, $query);
            });
        }
    }

    public function whenReceiveCall(\Closure $call): void
    {
        $this->parent = $call;
    }

    /**
     * @throws OperatorNotValid
     * @throws OperatorNotExist
     */
    public function filter(): Builder
    {
        if (! $this->filters || ! $this->rows) {
            return $this->builder;
        }

        foreach ($this->filters as $filter) {
            /** @var FilterableType|TypeFactory|CustomFactory $type */
            $type = $this->rows[$filter['field']];

            if ($type instanceof CustomFactory) {
                $call = $this->parent;
                $field = Str::replace('_', ' ', $filter['field']);
                $call(
                    Str::camel("filter {$field}"),
                    $this->builder,
                    $filter['operator'],
                    $filter['value']
                );
            } elseif ($type instanceof TypeFactory) {
                $this->handleTypeFactory($type, $filter);
            } else {
                $this->handle($type, $filter, $this->builder);
            }
        }

        return $this->builder;
    }
}
