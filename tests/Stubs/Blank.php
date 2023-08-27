<?php

namespace Albet\LaravelFilterable\Tests\Stubs;

use Albet\LaravelFilterable\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @method static Builder filter()
 */
class Blank extends Model
{
    use Filterable;
}
