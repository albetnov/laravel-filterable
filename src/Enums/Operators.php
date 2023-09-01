<?php

namespace Albet\LaravelFilterable\Enums;

use Illuminate\Support\Collection;

enum Operators: string
{
    case EQ = 'eq';
    case NEQ = 'neq';
    case CONTAINS = 'contains';
    case NOT_CONTAINS = 'not_contains';
    case STARTS_WITH = 'starts_with';
    case ENDS_WITH = 'ends_with';
    case IN = 'in';
    case NOT_IN = 'not_in';
    case HAVE_ALL = 'have_all';
    case GT = 'gt';
    case GTE = 'gte';
    case LT = 'lt';
    case LTE = 'lte';

    public static function toCollection(): Collection
    {
        return collect(self::cases())->pluck('value');
    }

    /**
     * @param array<Operators> $operators
     * @return Collection
     */
    public static function toValues(array $operators): Collection
    {
        return collect($operators)->map(fn (Operators $item) => $item->value);
    }
}
