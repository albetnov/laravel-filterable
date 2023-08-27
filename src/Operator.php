<?php

namespace Albet\LaravelFilterable;

use Illuminate\Support\Str;

class Operator
{
    private static array $supportedOperators = ['eq', 'neq', 'contains', 'starts_with', 'ends_with', 'not_contains', 'in', 'not_in', 'have_all', 'gt', 'lt', 'gte', 'lte'];

    public static function getAllOperators(): array
    {
        return self::$supportedOperators;
    }

    public static function except(array $items): array
    {
        return collect(self::$supportedOperators)->diff($items)->values()->toArray();
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

    public static function isTextOperator(string $selectedOperator): bool
    {
        return in_array($selectedOperator, ['eq', 'neq', 'contains', 'not_contains', 'starts_with', 'ends_with', 'in', 'not_in', 'have_all']);
    }

    public static function isNumberOperator(string $selectedOperator): bool
    {
        return in_array($selectedOperator, ['eq', 'neq', 'gt', 'lt', 'gte', 'lte']);
    }

    public static function isDateOperator(string $selectedOperator): bool
    {
        return in_array($selectedOperator, ['eq', 'neq', 'gt', 'lt', 'gte', 'lte', 'in', 'not_in']);
    }
}
