<?php

use Albet\LaravelFilterable\Handler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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

it("Can handle date", function () {
    /** @var Builder|MockInterface $builder */
    $builder = mock(Builder::class);
    $builder->shouldReceive('whereDate')->with('booked_at', '>', Carbon::class)->once();

    $handler = new Handler(
        builder: $builder,
        column: 'booked_at',
        operator: 'gt',
        value: '8/2/2023'
    );

    $handler->handleDate();
});

it("Can handle two dates (in)", function () {
    /** @var Builder|MockInterface $builder */
    $builder = mock(Builder::class);
    $builder->shouldReceive('whereDate')->with('booked_at', '>=', Carbon::class)->once()
        ->andReturnSelf();

    $builder->shouldReceive('whereDate')->with('booked_at', '<=', Carbon::class)->once()
        ->andReturnSelf();

    $handler = new Handler(
        builder: $builder,
        column: "booked_at",
        operator: "in",
        value: '8/2/2023,14/2/2023'
    );

    $handler->handleDate();
});

it("Can handle two dates (not_in)", function () {
    /** @var Builder|MockInterface $builder */
    $builder = mock(Builder::class);
    $builder->shouldReceive('whereDate')->with('booked_at', '<', Carbon::class)->once()
        ->andReturnSelf();

    $builder->shouldReceive('orWhereDate')->with('booked_at', '>', Carbon::class)->once()
        ->andReturnSelf();

    $handler = new Handler(
        builder: $builder,
        column: "booked_at",
        operator: "not_in",
        value: '8/2/2023,10/2/2023'
    );

    $handler->handleDate();
});
