<?php

use Albet\LaravelFilterable\Operator;

it("Get all operatores return supported operators", function () {
    expect(Operator::getAllOperators())->toBeArray()
        ->toContain('eq', 'neq', 'contains', 'starts_with', 'ends_with', 'not_contains', 'in', 'not_in', 'have_all', 'gt', 'lt', 'gte', 'lte');
});

it("Except works accordingly", function () {
    expect(Operator::except(['eq']))->not->toHaveKey('eq');
});

it("getQueryOperator return matched sql query operator", function () {
    expect(Operator::getQueryOperator("eq"))->toBe("=")
        ->and(Operator::getQueryOperator("neq"))->toBe("!=")
        ->and(Operator::getQueryOperator('contains'))->toBe('LIKE')
        ->and(Operator::getQueryOperator('starts_with'))->toBe('LIKE')
        ->and(Operator::getQueryOperator('ends_with'))->toBe('LIKE')
        ->and(Operator::getQueryOperator('not_contains'))->toBe('NOT LIKE')
        ->and(Operator::getQueryOperator('gt'))->toBe('>')
        ->and(Operator::getQueryOperator('gte'))->toBe('>=')
        ->and(Operator::getQueryOperator('lt'))->toBe('<')
        ->and(Operator::getQueryOperator('lte'))->toBe('<=');
});

it("parseOperatorValue return matched sql query value", function () {
    expect(Operator::parseOperatorValue("contains", 'test'))->toBe('%test%')
        ->and(Operator::parseOperatorValue("not_contains", 'test'))->toBe('%test%')
        ->and(Operator::parseOperatorValue('starts_with', 'test'))->toBe('test%')
        ->and(Operator::parseOperatorValue('ends_with', 'test'))->toBe('%test')
        ->and(Operator::parseOperatorValue('in', 'singlevalue'))->toBeArray()->toContain('singlevalue')
        ->and(Operator::parseOperatorValue('in', 'multi,value'))->toBeArray()->toContain('multi', 'value')
        ->and(Operator::parseOperatorValue('not_in', 'multi,value'))->toBeArray()->toContain('multi', 'value');
});

it("can validate text operator", function () {
     $lists = ['eq', 'neq', 'contains', 'starts_with', 'ends_with', 'not_contains', 'in', 'not_in', 'have_all'];

     foreach ($lists as $list) {
         expect(Operator::isTextOperator($list))->toBeTrue();
     }

     $notValidLists = ['gt', 'lt', 'gte', 'lte'];

     foreach($notValidLists as $notValidList) {
         expect(Operator::isTextOperator($notValidList))->toBeFalse();
     }
});

it("can validate number operator", function () {
    $lists = ['eq', 'neq', 'gt', 'lt', 'gte', 'lte'];

    foreach ($lists as $list) {
        expect(Operator::isNumberOperator($list))->toBeTrue();
    }

    $notValidList = Operator::except($lists);

    foreach ($notValidList as $list) {
        expect(Operator::isNumberOperator($list))->toBeFalse();
    }
});

it("can validate date operator", function () {
    $lists = ['eq', 'neq', 'gt', 'lt', 'gte', 'lte', 'in', 'not_in'];

    foreach ($lists as $list) {
        expect(Operator::isDateOperator($list))->toBeTrue();
    }

    $notValidLists = Operator::except($lists);

    foreach ($notValidLists as $notValidList) {
        expect(Operator::isDateOperator($notValidList))->toBeFalse();
    }
});
