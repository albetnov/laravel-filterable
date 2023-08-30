<?php

namespace Albet\LaravelFilterable\Enums;

use Albet\LaravelFilterable\TypeFactory;

enum FilterableType
{
    case TEXT;
    case NUMBER;
    case DATE;
    case BOOLEAN;

    public function limit(array $allowedOperators): TypeFactory
    {
        return (new TypeFactory($this))->limit($allowedOperators);
    }
}
