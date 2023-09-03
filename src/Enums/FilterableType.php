<?php

namespace Albet\LaravelFilterable\Enums;

use Albet\LaravelFilterable\Factories\CustomFactory;
use Albet\LaravelFilterable\Factories\TypeFactory;
use Illuminate\Database\Eloquent\Builder;

enum FilterableType
{
    case TEXT;
    case NUMBER;
    case DATE;
    case BOOLEAN;

    /**
     * @param  array<Operators>  $allowedOperators
     */
    public function limit(array $allowedOperators): TypeFactory
    {
        return (new TypeFactory($this))->limit($allowedOperators);
    }


    /**
     * @param string $relationship
     * @param (callable(Builder): void)|null $condition
     * @return TypeFactory
     *
     */
    public function related(string $relationship, callable $condition = null): TypeFactory
    {
        return (new TypeFactory($this))->related($relationship, $condition);
    }

    /**
     * @param  array<Operators>|null  $allowedOperators
     */
    public static function custom(array $allowedOperators = null): CustomFactory
    {
        if ($allowedOperators) {
            $factory = new TypeFactory();
            $factory->limit($allowedOperators);

            return new CustomFactory($factory);
        }

        return new CustomFactory();
    }
}
