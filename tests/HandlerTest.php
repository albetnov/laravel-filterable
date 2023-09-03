<?php

use Albet\LaravelFilterable\Handler;
use Illuminate\Database\Eloquent\Builder;
use Mockery\MockInterface;

use function Pest\Laravel\partialMock;

it('Can handle text (default operator)', function () {
    /** @var Builder|MockInterface $builder */
    $builder = partialMock(Builder::class);
    $builder->shouldReceive('where')->with('random', '=', 'abc')->once();

    $handler = new Handler(
        builder: $builder,
        column: 'random',
        operator: 'eq',
        value: 'abc'
    );

    $handler->handleText();
});

it('Can handle text (in operator)', function () {
    /** @var Builder|MockInterface $builder */
    $builder = mock(Builder::class);
    $builder->shouldReceive('whereIn')->with('customers', ['a', 'b', 'c'])->once();

    $handler = new Handler(
        builder: $builder,
        column: 'customers',
        operator: 'in',
        value: 'a,b,c'
    );

    $handler->handleText();
});

it('Can handle text (not_in operator)', function () {
    /** @var Builder|MockInterface $builder */
    $builder = mock(Builder::class);
    $builder->shouldReceive('whereNotIn')->with('customers', ['a', 'b', 'c'])->once();

    $handler = new Handler(
        builder: $builder,
        column: 'customers',
        operator: 'not_in',
        value: 'a,b,c'
    );

    $handler->handleText();
});

it('Can handle text (have_all operator)', function () {
    /** @var Builder|MockInterface $builder */
    $builder = mock(Builder::class);
    $builder->shouldReceive('where')->with('customers', 'a')->once();

    $builder->shouldReceive('where')->with('customers', 'b')->once();

    $handler = new Handler(
        builder: $builder,
        column: 'customers',
        operator: 'have_all',
        value: 'a,b'
    );

    $handler->handleText();
});

it('Can handle number (non float)', function () {
    /** @var Builder|MockInterface $builder */
    $builder = mock(Builder::class);
    $builder->shouldReceive('where')->with('threads', '>', 10)->once();

    $handler = new Handler(
        builder: $builder,
        column: 'threads',
        operator: 'gt',
        value: '10'
    );

    $handler->handleNumber();
});

it('Can handle number (float)', function () {
    /** @var Builder|MockInterface $builder */
    $builder = mock(Builder::class);
    $builder->shouldReceive('where')->with('threads', '>', 10.2)->once();

    $handler = new Handler(
        builder: $builder,
        column: 'threads',
        operator: 'gt',
        value: '10.2'
    );

    $handler->handleNumber();
});
