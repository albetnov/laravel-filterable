<?php

namespace Albet\LaravelFilterable\Traits;

use Albet\LaravelFilterable\Exceptions\PropertyNotExist;
use Albet\LaravelFilterable\Filter;
use Albet\LaravelFilterable\Operator;
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
        if (method_exists($this, 'getRows')) {
            return $this->getRows();
        }

        if (property_exists($this, 'rows')) {
            return $this->rows;
        }

        throw new PropertyNotExist();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PropertyNotExist
     */
    public function scopeFilter(Builder $query): Builder
    {
        $request = request();

        $request->validate([
            'filters.*.field' => ['required', 'string', Rule::in(collect($this->getFilterable())->keys()->toArray())],
            'filters.*.operator' => ['required', Rule::in(Operator::getAllOperators())],
            'filters.*.value' => 'required',
        ]);

        $filter = new Filter($query, $request->get('filters'), $this->getFilterable());

        return $filter->filter();
    }
}
