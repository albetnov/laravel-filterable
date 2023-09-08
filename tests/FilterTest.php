<?php

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Enums\Operators;
use Albet\LaravelFilterable\Exceptions\OperatorNotExist;
use Albet\LaravelFilterable\Exceptions\OperatorNotValid;
use Albet\LaravelFilterable\Filter;
use Albet\LaravelFilterable\Tests\Helpers;
use Albet\LaravelFilterable\Tests\Stubs\Flight;
use Illuminate\Database\Eloquent\Builder;

use function Pest\Laravel\partialMock;

it('can filter even if no payload is passed', function () {
    $builder = app(Builder::class);

    $model = new Flight();

    $builder->setModel($model);

    $filter = new Filter($builder, $model, null, null);

    expect($filter->filter())->toBeInstanceOf(Builder::class);
});

it('can filter with payload', function () {
    $model = new Flight();
    $builder = Helpers::makeBuilder($model);
    $filter = new Filter($builder, $model, [
        [
            'opr' => 'eq',
            'val' => '1',
            'field' => 'flight_no',
        ],
    ], [
        'flight_no' => FilterableType::NUMBER,
    ]);

    expect($filter->filter()->toRawSql())->toEqual('select * from "flights" where "flight_no" = 1');
});

it('nothing filtered due to filterableColumns is not defined', function () {
    $model = new Flight();
    $filter = new Filter(
        Helpers::makeBuilder($model),
        $model,
        [
            [
                'opr' => 'eq',
                'val' => '1',
                'field' => 'flight_no',
            ],
        ],
        []
    );

    expect($filter->filter()->toRawSql())->toEqual('select * from "flights"');
});

it('can filter operator using allowedOperators', function () {
    $model = new Flight();
    $filter = new Filter(
        Helpers::makeBuilder($model),
        $model,
        [[
            'opr' => 'eq',
            'val' => '1',
            'field' => 'flight_no',
        ]],
        [
            'flight_no' => FilterableType::NUMBER->limit([Operators::EQ]),
        ]
    );

    expect($filter->filter()->toRawSql())->toEqual('select * from "flights" where "flight_no" = 1');

    $filter = new Filter(
        Helpers::makeBuilder($model),
        $model,
        [[
            'opr' => 'neq',
            'val' => '1',
            'field' => 'flight_no',
        ]],
        [
            'flight_no' => FilterableType::NUMBER->limit([Operators::EQ]),
        ]
    );

    expect($filter->filter())->toThrow(OperatorNotExist::class);
})->throws(OperatorNotExist::class);

it('can filter relationship columns', function () {
    $model = new Flight();
    $filter = new Filter(
        Helpers::makeBuilder($model),
        $model,
        [[
            'opr' => 'eq',
            'val' => 'some name',
            'field' => 'ticket_name',
        ]],
        [
            'ticket_name' => FilterableType::TEXT->limit([Operators::EQ])->related('ticket'),
        ]
    );

    expect($filter->filter()->toRawSql())->toEqual(trim(<<<'sql'
        select * from "flights" where exists (select * from "tickets" where "flights"."id" = "tickets"."flight_id" and "ticket_name" = 'some name')
    sql
    ));
});

it('throws operator not valid', function () {
    $model = new Flight();
    $filter = new Filter(
        Helpers::makeBuilder($model),
        $model,
        [[
            'opr' => 'gt',
            'val' => 'testing',
            'field' => 'flight_no',
        ]],
        [
            'flight_no' => FilterableType::TEXT,
        ]
    );

    $filter->filter();
})->throws(OperatorNotValid::class);

it('can add extra condition for relation filter', function () {
    $model = new Flight();
    $filter = new Filter(
        Helpers::makeBuilder($model),
        $model,
        [[
            'opr' => 'eq',
            'val' => '120',
            'field' => 'ticket_no',
        ]],
        [
            'ticket_no' => FilterableType::NUMBER->related('ticket', fn (Builder $query) => $query->where('ticket_no', '>', 100)),
        ]
    );

    expect($filter->filter()->toRawSql())->toEqual(trim(<<<'sql'
        select * from "flights" where exists (select * from "tickets" where "flights"."id" = "tickets"."flight_id" and "ticket_no" > 100 and "ticket_no" = 120)
    sql
    ));
});

it('can filter custom', function () {
    /** @var Flight|Mockery\MockInterface $model */
    $model = partialMock(Flight::class);

    $model->shouldReceive('filterCustom')
        ->with(Builder::class, 'eq', 'custom')
        ->once();

    $filter = new Filter(
        Helpers::makeBuilder($model),
        $model,
        [[
            'opr' => 'eq',
            'val' => 'custom',
            'field' => 'custom',
        ]],
        [
            'custom' => FilterableType::custom(),
        ]
    );

    $filter->filter();
});
