<?php

use Albet\LaravelFilterable\Enums\Operators;
use Illuminate\Support\Collection;

it("Converts each of the case to it's backed enums", function() {
    $values = Operators::toCollection();

    expect($values)->toBeInstanceOf(Collection::class)
    ->and($values->toArray())
        ->toEqual(['eq', 'neq', 'contains', 'not_contains', 'starts_with', 'ends_with', 'in', 'not_in', 'have_all',
            'gt', 'gte', 'lt', 'lte']);
});

it("Converts each values to enums", function () {
    $operators = [Operators::EQ, Operators::NEQ, Operators::CONTAINS, Operators::NOT_CONTAINS,
        Operators::STARTS_WITH, Operators::ENDS_WITH, Operators::IN, Operators::NOT_IN, Operators::HAVE_ALL,
        Operators::GT, Operators::GTE, Operators::LT, Operators::LTE];
    $enums = Operators::toValues($operators);

    $values = $enums->toArray();

    expect($enums)->toBeInstanceOf(Collection::class)
    ->and($values)->toEqual(['eq', 'neq', 'contains', 'not_contains', 'starts_with', 'ends_with', 'in', 'not_in', 'have_all',
            'gt', 'gte', 'lt', 'lte']);
});
