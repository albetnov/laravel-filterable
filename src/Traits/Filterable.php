<?php

namespace Albet\LaravelFilterable\Traits;

use Albet\LaravelFilterable\Enums\Operators;
use Albet\LaravelFilterable\Exceptions\OperatorNotExist;
use Albet\LaravelFilterable\Exceptions\OperatorNotValid;
use Albet\LaravelFilterable\Exceptions\PropertyNotExist;
use Albet\LaravelFilterable\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait Filterable
{
    /**
     * @throws PropertyNotExist
     */
    private function getFilterable(): array
    {
        if (method_exists($this, 'filterableColumns')) {
            return $this->filterableColumns();
        }

        if (property_exists($this, 'filterableColumns')) {
            return $this->filterableColumns;
        }

        throw new PropertyNotExist();
    }

    /**
     * @throws PropertyNotExist
     * @throws NotFoundExceptionInterface
     * @throws OperatorNotValid
     * @throws ContainerExceptionInterface
     * @throws OperatorNotExist
     */
    public function scopeFilter(Builder $query): Builder
    {
        $request = request();

        $request->validate([
            'filters.*.field' => ['required', 'string', Rule::in(collect($this->getFilterable())->keys()->toArray())],
            'filters.*.operator' => ['required', Rule::in(Operators::toCollection()->toArray())],
            'filters.*.value' => 'required',
        ]);

        $filter = new Filter($query, $request->get('filters'), $this->getFilterable());

        $filter->whenReceiveCall(fn($method, $arguments) => $this->{$method}(...$arguments));

        return $filter->filter();
    }
}
