<?php

use Albet\LaravelFilterable\Tests\Stubs\Flight;
use Albet\LaravelFilterable\Tests\Stubs\Tickets;

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
