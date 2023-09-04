<?php

namespace Albet\LaravelFilterable;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Enums\Operators;
use Albet\LaravelFilterable\Exceptions\OperatorNotExist;
use Albet\LaravelFilterable\Exceptions\OperatorNotValid;
use Albet\LaravelFilterable\Factories\CustomFactory;
use Albet\LaravelFilterable\Factories\TypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Filter
{
    public function __construct(
        private readonly Builder $builder,
        private readonly Model $parent,
        private readonly ?array $filters,
        private readonly ?array $rows,
    ) {
    }

    /**
     * @throws OperatorNotExist
     */
    private function matchCustomOperators(array $customOperators, string $operator): void
    {
        if (! Operators::toValues($customOperators)->contains($operator)) {
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
     * @throws OperatorNotValid
     */
    private function handleTypeFactory(TypeFactory $typeFactory, array $filter): void
    {
        /** @phpstan-ignore-next-line */
        if ($typeFactory->getOperators()) {
            /** @phpstan-ignore-next-line */
            $this->matchCustomOperators($typeFactory->getOperators(), $filter['operator']);
        }

        /** @phpstan-ignore-next-line */
        $relationship = $typeFactory->getRelated();
        if ($relationship) {
            $this->builder->whereHas($relationship['relationship'], function (Builder $query) use ($relationship, $typeFactory, $filter) {
                if ($relationship['condition']) {
                    $relationship['condition']($query);
                }
                /** @phpstan-ignore-next-line */
                $this->handle($typeFactory->getType(), $filter, $query);
            });

            return;
        }

        /** @phpstan-ignore-next-line */
        $this->handle($typeFactory->getType(), $filter, $this->builder);
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
                $field = Str::replace('_', ' ', $filter['field']);
                $method = Str::camel("filter $field");
                $this->parent->{$method}($this->builder, $filter['operator'], $filter['value']);
            } elseif ($type instanceof TypeFactory) {
                $this->handleTypeFactory($type, $filter);
            } else {
                $this->handle($type, $filter, $this->builder);
            }
        }

        return $this->builder;
    }
}
