<?php

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Enums\Operators;
use Albet\LaravelFilterable\Factories\CustomFactory;
use Albet\LaravelFilterable\Factories\TypeFactory;
use Illuminate\Database\Eloquent\Builder;

it('can construct limited type factory', function () {
    $type = FilterableType::TEXT->limit([Operators::EQ, Operators::STARTS_WITH]);

    expect($type)->toBeInstanceOf(TypeFactory::class)
        ->and($type->getOperators())->toBeArray()->toHaveCount(2)->toEqual([Operators::EQ, Operators::STARTS_WITH])
        ->and($type->getType())->toBe(FilterableType::TEXT);
});

it('throws invalid argument exception if not valid operators being passed', function () {
    FilterableType::NUMBER->limit([Operators::ENDS_WITH]);
})->throws(\InvalidArgumentException::class);

it('can construct related type factory (without condition)', function () {
    $type = FilterableType::DATE->related('flights');

    expect($type)->toBeInstanceOf(TypeFactory::class)
        ->and($type->getRelated())->toBeArray()->toHaveCount(2)->toEqual([
            'relationship' => 'flights',
            'condition' => null,
        ])
        ->and($type->getType())->toBe(FilterableType::DATE);
});

it('can construct related type factory (with condition)', function () {
    $type = FilterableType::DATE->related('flights', function (Builder $builder) {
        $builder->where('flight_no', '>', 1);
    });

    $related = $type->getRelated();

    expect($type)->toBeInstanceOf(TypeFactory::class)
        ->and($related)->toBeArray()->toHaveCount(2)
        ->and($related['relationship'])->toEqual('flights')
        ->and($related['condition'])->toBeInstanceOf(Closure::class)
        ->and($type->getType())->toBe(FilterableType::DATE);
});

it('can construct custom factory (without limit)', function () {
    $type = FilterableType::custom();

    expect($type)->toBeInstanceOf(CustomFactory::class)
        ->and($type->get())->toBeNull();
});

it('can construct custom factory (with limit)', function () {
    $type = FilterableType::custom([Operators::EQ, Operators::STARTS_WITH]);

    expect($type)->toBeInstanceOf(CustomFactory::class)
        ->and($type->get())->toBeArray()->toHaveCount(2)->toEqual([Operators::EQ, Operators::STARTS_WITH]);
});
