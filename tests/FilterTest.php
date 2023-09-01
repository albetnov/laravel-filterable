<?php

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Enums\Operators;
use Albet\LaravelFilterable\Exceptions\OperatorNotExist;
use Albet\LaravelFilterable\Exceptions\OperatorNotValid;
use Albet\LaravelFilterable\Filter;
use Albet\LaravelFilterable\Tests\Helpers;
use Albet\LaravelFilterable\Tests\Stubs\Flight;
use Illuminate\Database\Eloquent\Builder;

it("can filter even if no payload is passed", function () {
    $builder = app(Builder::class);

    $builder->setModel(new Flight());

    $filter = new Filter($builder, null, null);

    expect($filter->filter())->toBeInstanceOf(Builder::class);
});

it("can filter with payload", function () {
    $builder = Helpers::makeBuilder(new Flight());
    $filter = new Filter($builder, [
        [
            'operator' => 'eq',
            'value' => '1',
            'field' => 'flight_no'
        ],
    ], [
        'flight_no' => FilterableType::NUMBER
    ]);

    expect($filter->filter()->toRawSql())->toEqual('select * from "flights" where "flight_no" = 1');
});


it("nothing filtered due to filterableColumns is not defined", function () {
    $filter = new Filter(
        Helpers::makeBuilder(new Flight()),
        [
            [
                'operator' => 'eq',
                'value' => '1',
                'field' => 'flight_no'
            ],
        ],
        []
    );

    expect($filter->filter()->toRawSql())->toEqual('select * from "flights"');
});

it("can filter operator using allowedOperators", function () {
    $filter = new Filter(
        Helpers::makeBuilder(new Flight()),
        [[
            'operator' => 'eq',
            'value' => '1',
            'field' => 'flight_no'
        ]],
        [
            'flight_no' => FilterableType::NUMBER->limit([Operators::EQ])
        ]
    );

    expect($filter->filter()->toRawSql())->toEqual('select * from "flights" where "flight_no" = 1');

    $filter = new Filter(
        Helpers::makeBuilder(new Flight()),
        [[
            'operator' => 'neq',
            'value' => '1',
            'field' => 'flight_no'
        ]],
        [
            'flight_no' => FilterableType::NUMBER->limit([Operators::EQ])
        ]
    );

    expect($filter->filter())->toThrow(OperatorNotExist::class);
})->throws(OperatorNotExist::class);

it("can filter relationship columns", function () {
   $filter = new Filter(
       Helpers::makeBuilder(new Flight()),
       [[
           'operator' => 'eq',
           'value' => 'some name',
           'field' => 'ticket_name'
       ]],
       [
           'ticket_name' => FilterableType::TEXT->limit([Operators::EQ])->related('ticket')
       ]
   );

   expect($filter->filter()->toRawSql())->toEqual(trim(<<<'sql'
        select * from "flights" where exists (select * from "tickets" where "flights"."id" = "tickets"."flight_id" and "ticket_name" = 'some name')
    sql));
});

it("whenReceiveCall worked as expected to emit event outside context", function () {
    $filter = new Filter(
        Helpers::makeBuilder(new Flight()),
        [[
            'operator' => 'eq',
            'value' => 'testing',
            'field' => 'custom'
        ]],
        [
            'custom' => FilterableType::custom([Operators::EQ])
        ]
    );

    $filter->whenReceiveCall(function ($method, $builder, $operator, $value) {
        expect($method)->toEqual('filterCustom')
            ->and($builder)->toBeInstanceOf(Builder::class)
            ->and($operator)->toEqual('eq')
            ->and($value)->toEqual('testing');
    });

    $filter->filter();
});

it("throws operator not valid", function () {
    $filter = new Filter(
        Helpers::makeBuilder(new Flight()),
        [[
            'operator' => 'gt',
            'value' => 'testing',
            'field' => 'flight_no'
        ]],
        [
            'flight_no' => FilterableType::TEXT
        ]
    );

    $filter->filter();
})->throws(OperatorNotValid::class);

it("can add extra condition for relation filter", function () {
    $filter = new Filter(
        Helpers::makeBuilder(new Flight()),
        [[
            'operator' => 'eq',
            'value' => '120',
            'field' => 'ticket_no'
        ]],
        [
            'ticket_no' => FilterableType::NUMBER->related('ticket', fn (Builder $query) => $query->where('ticket_no', '>', 100))
        ]
    );

    expect($filter->filter()->toRawSql())->toEqual(trim(<<<'sql'
        select * from "flights" where exists (select * from "tickets" where "flights"."id" = "tickets"."flight_id" and "ticket_no" > 100 and "ticket_no" = 120)
    sql));
});
