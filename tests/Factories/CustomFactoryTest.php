<?php

use Albet\LaravelFilterable\Enums\Operators;
use Albet\LaravelFilterable\Factories\CustomFactory;
use Albet\LaravelFilterable\Factories\TypeFactory;

it('return array of allowedOperators', function () {
    $typeFactory = new TypeFactory();

    $customFactory = new CustomFactory($typeFactory->limit([Operators::EQ]));

    expect($customFactory->get())->toEqual([Operators::EQ]);
});
