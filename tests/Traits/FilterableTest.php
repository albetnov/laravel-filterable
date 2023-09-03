<?php

use Albet\LaravelFilterable\Exceptions\PropertyNotExist;
use Albet\LaravelFilterable\Tests\Helpers;
use Albet\LaravelFilterable\Tests\Stubs\Blank;
use Albet\LaravelFilterable\Tests\Stubs\Flight;
use Albet\LaravelFilterable\Tests\Stubs\Tickets;
use Illuminate\Validation\ValidationException;

it('can get columns from property', function () {
    $reflection = new \ReflectionClass(Tickets::class);

    $method = $reflection->getMethod('getFilterable');

    $method->setAccessible(true);

    $tickets = new Tickets();

    $result = $method->invoke($tickets);

    $prop = $reflection->getProperty('filterableColumns');

    expect($result)->toBeArray()->toEqual($prop->getValue($tickets));
});

it('can get columns from function', function () {
    $method = new \ReflectionMethod(Flight::class, 'getFilterable');

    $method->setAccessible(true);

    $flights = new Flight();

    $result = $method->invoke($flights);

    $definerMethod = new \ReflectionMethod(Flight::class, 'filterableColumns');
    $definerMethod->setAccessible(true);

    $definer = $definerMethod->invoke($flights);

    expect($result)->toBeArray()->toEqual($definer);
});

it("throws a validation error for invalid payload", function () {
    Helpers::fakeFilter('not_exist', 'eq', 'abc');

    expect(fn() => Flight::filter())->toThrow(ValidationException::class);

    Helpers::fakeFilter('flight_no', 'new_opr', 'abc');

    expect(fn() => Flight::filter())->toThrow(ValidationException::class);

    Helpers::fakeFilter('flight_no', 'neq', '');

    expect(fn() => Flight::filter())->toThrow(ValidationException::class);
});

it("can perform a filter", function () {
    Helpers::fakeFilter('flight_no', 'gt', '10');

    expect(Flight::filter()->toRawSql())->toEqual('select * from "flights" where "flight_no" > 10');
});

it("throws PropertyNotExist", function () {
    Blank::filter();
})->throws(PropertyNotExist::class);
