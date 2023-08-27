<?php

namespace Albet\LaravelFilterable\Tests\Stubs;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @method static Builder filter()
 */
class Flight extends Model
{
    use Filterable;

    protected function getRows(): array {
        return [
            'flight_no' => FilterableType::NUMBER,
            'flight_type' => FilterableType::TEXT
        ];
    }
}
