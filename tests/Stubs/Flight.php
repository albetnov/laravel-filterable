<?php

namespace Albet\LaravelFilterable\Tests\Stubs;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Enums\Operators;
use Albet\LaravelFilterable\Operator;
use Albet\LaravelFilterable\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static Builder filter()
 */
class Flight extends Model
{
    use Filterable;

    protected function filterableColumns(): array
    {
        return [
            'flight_no' => FilterableType::NUMBER,
            'flight_type' => FilterableType::TEXT,
            'ticket_name' => FilterableType::TEXT->limit([Operators::EQ, Operators::NEQ])->related('ticket'),
            'flight_staff' => FilterableType::NUMBER->limit([Operators::EQ, Operators::NEQ]),
            'ticket_no' => FilterableType::NUMBER->related('ticket', fn (Builder $query) => $query->where('ticket_no', '>', 100)),
            'custom' => FilterableType::custom([Operators::EQ]),
        ];
    }

    public function filterCustom(Builder $query, $operators, $value): void
    {
        $query->where('custom', '>', 100)->where('custom', Operator::getQueryOperator($operators), $value);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Tickets::class);
    }
}
