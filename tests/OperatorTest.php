<?php

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Operator;

it('Except works accordingly', function () {
    expect(Operator::except(['eq']))->not->toHaveKey('eq');
});

it('getQueryOperator return matched sql query operator', function () {
    expect(Operator::getQueryOperator('eq'))->toBe('=')
        ->and(Operator::getQueryOperator('neq'))->toBe('!=')
        ->and(Operator::getQueryOperator('contains'))->toBe('LIKE')
        ->and(Operator::getQueryOperator('starts_with'))->toBe('LIKE')
        ->and(Operator::getQueryOperator('ends_with'))->toBe('LIKE')
        ->and(Operator::getQueryOperator('not_contains'))->toBe('NOT LIKE')
        ->and(Operator::getQueryOperator('gt'))->toBe('>')
        ->and(Operator::getQueryOperator('gte'))->toBe('>=')
        ->and(Operator::getQueryOperator('lt'))->toBe('<')
        ->and(Operator::getQueryOperator('lte'))->toBe('<=');
});

it('parseOperatorValue return matched sql query value', function () {
    expect(Operator::parseOperatorValue('contains', 'test'))->toBe('%test%')
        ->and(Operator::parseOperatorValue('not_contains', 'test'))->toBe('%test%')
        ->and(Operator::parseOperatorValue('starts_with', 'test'))->toBe('test%')
        ->and(Operator::parseOperatorValue('ends_with', 'test'))->toBe('%test')
        ->and(Operator::parseOperatorValue('in', 'singlevalue'))->toBeArray()->toContain('singlevalue')
        ->and(Operator::parseOperatorValue('in', 'multi,value'))->toBeArray()->toContain('multi', 'value')
        ->and(Operator::parseOperatorValue('not_in', 'multi,value'))->toBeArray()->toContain('multi', 'value');
});

it('is text contains text only operator', function () {
    expect(Operator::is(FilterableType::TEXT, 'eq'))->toBeTrue()
        ->and(Operator::is(FilterableType::TEXT, ['eq', 'neq']))->toBeTrue()
        ->and(Operator::is(FilterableType::TEXT, 'gt'))->toBeFalse()
        ->and(Operator::is(FilterableType::TEXT, ['gt', 'lt']))->toBeFalse()
        ->and(Operator::is(FilterableType::TEXT, ['eq', 'gt']))->tobeFalse();
});

it('is number contains number only operator', function () {
    expect(Operator::is(FilterableType::NUMBER, 'eq'))->toBeTrue()
        ->and(Operator::is(FilterableType::NUMBER, ['eq', 'neq']))->toBeTrue()
        ->and(Operator::is(FilterableType::NUMBER, 'contains'))->toBeFalse()
        ->and(Operator::is(FilterableType::NUMBER, ['starts_with', 'contains']))->toBeFalse()
        ->and(Operator::is(FilterableType::NUMBER, ['eq', 'ends_with']))->tobeFalse();
});

it('is date contains date only operator', function () {
    expect(Operator::is(FilterableType::DATE, 'eq'))->toBeTrue()
        ->and(Operator::is(FilterableType::DATE, ['eq', 'neq']))->toBeTrue()
        ->and(Operator::is(FilterableType::DATE, 'contains'))->toBeFalse()
        ->and(Operator::is(FilterableType::DATE, ['starts_with', 'contains']))->toBeFalse()
        ->and(Operator::is(FilterableType::DATE, ['eq', 'ends_with']))->tobeFalse();
});

it('is boolean contains boolean only operator', function () {
    expect(Operator::is(FilterableType::BOOLEAN, 'eq'))->toBeTrue()
        ->and(Operator::is(FilterableType::BOOLEAN, ['eq', 'neq']))->toBeTrue()
        ->and(Operator::is(FilterableType::BOOLEAN, 'contains'))->toBeFalse()
        ->and(Operator::is(FilterableType::BOOLEAN, ['starts_with', 'contains']))->toBeFalse()
        ->and(Operator::is(FilterableType::BOOLEAN, ['eq', 'ends_with']))->tobeFalse();
});
