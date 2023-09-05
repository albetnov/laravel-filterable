<?php

namespace Albet\LaravelFilterable\Tests;

use Albet\LaravelFilterable\Operator;
use Albet\LaravelFilterable\Tests\Stubs\Flight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Helpers
{
    public static function fakeFilter(array|string $filters, array|string $operators, array|string $values): void
    {
        if (is_string($filters) && is_string($operators) && is_string($values)) {
            request()->merge([
                'filters' => [[
                    'field' => $filters,
                    'operator' => $operators,
                    'value' => $values,
                ]],
            ]);

            return;
        }

        $allFilters = [];
        foreach ($filters as $key => $filter) {
            $allFilters[] = [
                'field' => $filter,
                'operator' => $operators[$key],
                'value' => $values[$key],
            ];
        }

        request()->merge([
            'filters' => $allFilters,
        ]);
    }

    public static function makeBuilder(Model $model): Builder
    {
        $builder = app(Builder::class);

        $builder->setModel(new Flight());
        $builder->getGrammar()->setConnection($builder->getConnection());

        return $builder;
    }
}
