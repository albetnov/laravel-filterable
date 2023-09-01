<?php

namespace Albet\LaravelFilterable;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Enums\Operators;
use Illuminate\Support\Str;

class Operator
{
    public static function except(array $items): array
    {
        return Operators::toCollection()->diff($items)->values()->toArray();
    }

    private static function assertOperators(string|array $operators, array $allowedOperators): bool
    {
        if (is_string($operators)) {
            return Operators::toValues($allowedOperators)->contains($operators);
        }

        return Operators::toValues($allowedOperators)
                ->intersect($operators)->count() === count($operators);
    }

    public static function is(FilterableType $mode, string|array $operators): bool
    {
        return match ($mode) {
            FilterableType::TEXT => self::assertOperators(
                $operators,
                [Operators::EQ, Operators::NEQ, Operators::CONTAINS, Operators::NOT_CONTAINS, Operators::STARTS_WITH,
                    Operators::ENDS_WITH, Operators::IN, Operators::NOT_IN, Operators::HAVE_ALL]
            ),
            FilterableType::NUMBER => self::assertOperators($operators, [Operators::EQ, Operators::NEQ, Operators::GT,
                Operators::GTE, Operators::LT, Operators::LTE]),
            FilterableType::DATE => self::assertOperators($operators, [Operators::EQ, Operators::NEQ, Operators::GT,
                Operators::GTE, Operators::LT, Operators::LTE, Operators::IN, Operators::NOT_IN]),
            FilterableType::BOOLEAN => self::assertOperators($operators, [Operators::EQ, Operators::NEQ])
        };
    }

    public static function getQueryOperator(string $operator): string|false
    {
        return match ($operator) {
            'eq' => '=',
            'neq' => '!=',
            'contains', 'starts_with', 'ends_with' => 'LIKE',
            'not_contains' => 'NOT LIKE',
            'gt' => '>',
            'lt' => '<',
            'gte' => '>=',
            'lte' => '<=',
            default => false
        };
    }

    public static function parseOperatorValue(string $operator, string $value): string|array
    {
        return match ($operator) {
            'contains', 'not_contains' => "%$value%",
            'starts_with' => "$value%",
            'ends_with' => "%$value",
            'in', 'not_in', 'have_all' => Str::contains($value, ',') ? explode(',', $value) : [$value],
            default => $value
        };
    }
}
