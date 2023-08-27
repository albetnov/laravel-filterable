<?php

namespace Albet\LaravelFilterable\Tests;

use Albet\LaravelFilterable\Operator;

class Helpers
{
    public static function craftWhereQuery(array|string $columns, array|string $operators, array|string $values, bool $unwrapValue = false): string
    {
        if(is_string($columns) && is_string($operators) && is_string($values)) {
            $values = Operator::parseOperatorValue($operators, $values);
            $operators = Operator::getQueryOperator($operators);

            if(!$unwrapValue) {
                $values = "'$values'";
            }

            return "where \"$columns\" $operators $values";
        }

        $whereStmt = null;
        foreach ($columns as $key => $column) {
            $operator = Operator::getQueryOperator($operators[$key]);
            $value = Operator::parseOperatorValue($operators[$key], $values[$key]);

            if(!$unwrapValue) {
                $value = "'$value'";
            }

            if ($whereStmt) {
                $whereStmt .= "and \"$column\" $operator $value ";
            } else {
                $whereStmt = "where \"$column\" $operator $value ";
            }
        }

        return rtrim($whereStmt);
    }

    public static function fakeFilter(array|string $filters, array|string $operators, array|string $values): void
    {
        if (is_string($filters) && is_string($operators) && is_string($values)) {
            request()->merge([
                'filters' => [[
                    'field' => $filters,
                    'operator' => $operators,
                    'value' => $values
                ]]
            ]);

            return;
        }

        $allFilters = [];
        foreach ($filters as $key => $filter) {
            $allFilters[] = [
                'field' => $filter,
                'operator' => $operators[$key],
                'value' => $values[$key]
            ];
        }

        request()->merge([
            'filters' => $allFilters
        ]);
    }
}
