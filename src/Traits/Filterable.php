<?php

namespace Albet\LaravelFilterable\Traits;

use Albet\LaravelFilterable\Enums\Operators;
use Albet\LaravelFilterable\Exceptions\PropertyNotExist;
use Albet\LaravelFilterable\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

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

    public function scopeFilter(Builder $query): Builder
    {
        $request = request();

        $request->validate([
            'filters.*.field' => ['required', 'string', Rule::in(collect($this->getFilterable())->keys()->toArray())],
            'filters.*.operator' => ['required', Rule::in(Operators::toCollection()->toArray())],
            'filters.*.value' => 'required',
        ]);

        $filter = new Filter($query, $request->get('filters'), $this->getFilterable());

        return $filter->filter();
    }
}
