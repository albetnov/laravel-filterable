<?php

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Enums\Operators;
use Albet\LaravelFilterable\Factories\TypeFactory;

it("Can read enum type", function () {
    $typeFactory = FilterableType::NUMBER->limit([Operators::EQ]);

    expect($typeFactory->getType())->toBe(FilterableType::NUMBER);
});

it("Can limit operators", function () {
    $typeFactory = (new TypeFactory())->limit([Operators::NEQ]);

    expect($typeFactory->getOperators())->toEqual([Operators::NEQ]);
});

it("Can relate columns", function () {
    $related = (new TypeFactory())->related('column', function () {})->getRelated();

    expect($related['relationship'])->toEqual('column')
        ->and($related['condition'])->toBeInstanceOf(\Closure::class);
});
