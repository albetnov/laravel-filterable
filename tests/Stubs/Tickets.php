<?php

namespace Albet\LaravelFilterable\Tests\Stubs;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @method static Builder filter()
 */
class Tickets extends Model
{
    use Filterable;

    protected array $rows = [
        'ticket_id' => FilterableType::NUMBER,
        'ticket_no' => FilterableType::TEXT,
        'booked_at' => FilterableType::DATE,
        'is_sold' => FilterableType::BOOLEAN
    ];
}
