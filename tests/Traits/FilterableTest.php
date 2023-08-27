<?php

use Albet\LaravelFilterable\Tests\Helpers;
use Albet\LaravelFilterable\Tests\Stubs\Blank;
use Albet\LaravelFilterable\Tests\Stubs\Tickets;
use Albet\LaravelFilterable\Tests\Stubs\Flight;
use PHPUnit\TextUI\Help;
use Symfony\Component\HttpKernel\Exception\HttpException;

it("return correct eq and neq", function () {
    $fields = array_fill(0, 2, "ticket_no");
    $operators = ["eq", "neq"];
    $values = array_fill(0, 2, "abc");

    Helpers::fakeFilter($fields, $operators, $values);

    expect(Tickets::filter()->toRawSql())->toContain(Helpers::craftWhereQuery($fields, $operators, $values));

    $fields = array_fill(0, 2, "ticket_id");
    $values = array_fill(0,2,5);

    Helpers::fakeFilter($fields, $operators, $values);

    expect(Tickets::filter()->toRawSql())->toContain(Helpers::craftWhereQuery($fields, $operators, $values, true));

    $fields = array_fill(0, 2, "booked_at");
    $values = array_fill(0,2,"8/20/2023");

    Helpers::fakeFilter($fields, $operators, $values);

    expect(Tickets::filter()->toRawSql())->toEqual(trim(<<<'sql'
        select * from "tickets" where strftime('%Y-%m-%d', "booked_at") = cast('2023-08-20' as text) and strftime('%Y-%m-%d', "booked_at") != cast('2023-08-20' as text)
    sql));
});

it("can load getRows as alternative of rows property", function () {
    $fields = "flight_no";
    $operators = 'eq';
    $values = 10;

    Helpers::fakeFilter($fields, $operators, $values);

    expect(Flight::filter()->toRawSql())->toContain(Helpers::craftWhereQuery($fields, $operators, $values, true));
});

it("throw an exception when no rows attribute is passed", function () {
    Blank::filter();
})->throws(\InvalidArgumentException::class, 'getRows() or $rows is not exist');

it("return correct starts_with query", function () {
    $field = 'ticket_no';
    $operator = 'starts_with';
    $value = 'abc';
    Helpers::fakeFilter($field, $operator, $value);

    expect(Tickets::filter()->toRawSql())->toContain(Helpers::craftWhereQuery($field, $operator, $value));
});

it("return correct ends_with query", function () {
    $field = 'ticket_no';
    $operator = 'ends_with';
    $value = 'abc';
    Helpers::fakeFilter($field, $operator, $value);
    expect(Tickets::filter()->toRawSql())->toContain(Helpers::craftWhereQuery($field, $operator, $value));
});

it("supports all types of text operator", function () {
    $operators = ['neq', 'contains', 'not_contains', 'not_contains'];
    $fields = array_fill(0, count($operators), 'ticket_no'); // match operators
    $values = array_fill(0, count($operators), 'test');
    Helpers::fakeFilter(
        $fields,
        $operators,
        $values
    );

    expect(Tickets::filter()->toRawSql())->toContain(Helpers::craftWhereQuery($fields, $operators, $values));
});

it("return correct in query", function () {
    Helpers::fakeFilter('ticket_no', 'in', 'multi,value');

    expect(Tickets::filter()->toRawSql())->toContain('where', "in ('multi', 'value')");

    Helpers::fakeFilter('booked_at', 'in', '8/23/2023,8/25/2023');

    expect(Tickets::filter()->toRawSql())->toEqual(trim(<<<'sql'
        select * from "tickets" where strftime('%Y-%m-%d', "booked_at") >= cast('2023-08-23' as text) and strftime('%Y-%m-%d', "booked_at") <= cast('2023-08-25' as text)
    sql));
});

it("return correct not_in query", function () {
    Helpers::fakeFilter('ticket_no', 'not_in', 'multi,value');

    expect(Tickets::filter()->toRawSql())->toContain('where', "not in ('multi', 'value')");

    Helpers::fakeFilter('booked_at', 'not_in', '8/23/2023,8/25/2023');

    expect(Tickets::filter()->toRawSql())->toEqual(trim(<<<'sql'
        select * from "tickets" where (strftime('%Y-%m-%d', "booked_at") < cast('2023-08-23' as text) or strftime('%Y-%m-%d', "booked_at") > cast('2023-08-25' as text))
    sql));
});

it("generate where and query using have_all", function () {
    Helpers::fakeFilter("ticket_no", "have_all", "multi,value");

    expect(Tickets::filter()->toRawSql())->toContain(Helpers::craftWhereQuery(
        array_fill(0, 2, "ticket_no"),
        array_fill(0, 2, "eq"),
        ['multi', 'value']
    ));
});

it("generate gt, lt, gte, lte queries", function () {
    $operators = ['lt', 'gt', 'lte', 'gte'];
    $fields = array_fill(0, count($operators), "ticket_id");
    $values = array_fill(0, count($operators), 10);
    Helpers::fakeFilter($fields, $operators, $values);

    expect(Tickets::filter()->toRawSql())->toContain(Helpers::craftWhereQuery($fields, $operators, $values, true));

    $fields = array_fill(0, count($operators), "booked_at");
    $values = array_fill(0, count($operators), "8/20/2023");
    Helpers::fakeFilter($fields, $operators, $values);

    expect(Tickets::filter()->toRawSql())->toContain('<', '>', '>=', '<=', '2023-08-20');
});

it("throws http exception for invalid text operator", function () {
    Helpers::fakeFilter('ticket_no', 'gt', 'abc');
    Tickets::filter();
})->throws(HttpException::class, "Invalid operator for text type");

it("string without commas won't throw error (in, not_in, have_all)", function () {
    Helpers::fakeFilter(
        array_fill(0, 2, 'ticket_no'),
        ['in', 'not_in', 'have_all'],
        array_fill(0,2, 'test')
    );

    Tickets::filter();
})->throwsNoExceptions();

it("throws http exception for invalid number operator", function () {
    Helpers::fakeFilter('ticket_id', 'starts_with', 'abc');

    Tickets::filter();
})->throws(HttpException::class, "Invalid operator for number type");

it("throws http exception for invalid date operator", function () {
    Helpers::fakeFilter('booked_at', 'ends_with', 'abc');

    Tickets::filter();
})->throws(HttpException::class, 'Invalid operator for date type');
