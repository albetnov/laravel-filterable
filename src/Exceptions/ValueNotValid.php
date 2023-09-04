<?php

namespace Albet\LaravelFilterable\Exceptions;

use Albet\LaravelFilterable\Enums\FilterableType;

class ValueNotValid extends \Exception
{
    public function __construct(readonly string $value, readonly FilterableType $type)
    {
        parent::__construct("{$this->value} is not valid {$this->type->name}");
    }
}
