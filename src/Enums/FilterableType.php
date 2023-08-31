<?php

namespace Albet\LaravelFilterable\Enums;

use Albet\LaravelFilterable\Factories\CustomFactory;
use Albet\LaravelFilterable\Factories\TypeFactory;

enum FilterableType
{
    case TEXT;
    case NUMBER;
    case DATE;
    case BOOLEAN;

    /**
     * @param array<Operators> $allowedOperators
     * @return TypeFactory
     */
    public function limit(array $allowedOperators): TypeFactory
    {
        return (new TypeFactory($this))->limit($allowedOperators);
    }

    public function related(string $relationship, ?callable $condition = null): TypeFactory
    {
        return (new TypeFactory($this))->related($relationship, $condition);
    }

    /**
     * @param array<Operators>|null $allowedOperators
     * @return CustomFactory
     */
    public static function custom(?array $allowedOperators = null): CustomFactory
    {
        if($allowedOperators) {
            $factory = new TypeFactory();
            $factory->limit($allowedOperators);
            return new CustomFactory($factory);
        }

        return new CustomFactory();
    }
}
