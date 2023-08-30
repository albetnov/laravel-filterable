<?php

namespace Albet\LaravelFilterable;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Exceptions\OperatorNotExist;
use Albet\LaravelFilterable\Exceptions\OperatorNotValid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Filter
{
    private ?array $customOperators = null;

    public function __construct(
        private readonly Builder $builder,
        private readonly ?array  $filters,
        private readonly ?array  $rows
    )
    {
    }

    private function constructWhere(array $values, string $column): void
    {
        foreach ($values as $value) {
            $this->builder->where($column, $value);
        }
    }

    /**
     * @throws OperatorNotExist
     */
    private function matchCustomOperators(string $operator): void
    {
        if ($this->customOperators && !in_array($operator, $this->customOperators)) {
            throw new OperatorNotExist($operator);
        }
    }

    private function handleText(string $column, string $operator, string $text): void
    {
        $queryOperator = Operator::getQueryOperator($operator);
        $fieldValue = Operator::parseOperatorValue($operator, $text);

        match ($operator) {
            'in' => $this->builder->whereIn($column, $fieldValue),
            'not_in' => $this->builder->whereNotIn($column, $fieldValue),
            'have_all' => $this->constructWhere($fieldValue, $column),
            default => $this->builder->where($column, $queryOperator, $fieldValue)
        };
    }

    private function handleNumber(string $column, string $operator, string $number): void
    {
        $operator = Operator::getQueryOperator($operator);
        $fieldValue = Str::contains($number, '.') ? (float)$number : (int)$number;

        $this->builder->where($column, $operator, $fieldValue);
    }

    private function handleDate(string $column, string $operator, string $date): void
    {
        $fieldValue = Operator::parseOperatorValue($operator, $date);

        if (is_array($fieldValue) && count($fieldValue) === 2) {
            $fieldValue = collect($fieldValue)->map(fn(string $item) => Carbon::createFromFormat('n/j/Y', $item))
                ->toArray();

            if ($operator === 'in') {
                $this->builder->whereDate($column, '>=', $fieldValue[0])->whereDate($column, '<=', $fieldValue[1]);

                return;
            }

            if ($operator === 'not_in') {
                $this->builder->whereDate($column, '<', $fieldValue[0])->orWhereDate($column, '>', $fieldValue[1]);

                return;
            }
        }

        $queryOperator = Operator::getQueryOperator($operator);

        $fieldValue = Carbon::createFromFormat('n/j/Y', $date);

        $this->builder->whereDate($column, $queryOperator, $fieldValue);
    }

    private function handleBoolean(string $column, string $operator, string $bool): void
    {
        if (!in_array($bool, ['0', '1'])) {
            abort(400, 'Invalid value for boolean filter');
        }

        $operator = Operator::getQueryOperator($operator);

        $this->builder->where($column, $operator, (bool)$bool);
    }

    /**
     * @throws OperatorNotValid
     * @throws OperatorNotExist
     */
    public function filter(): Builder
    {
        if (!$this->filters || !$this->rows) {
            return $this->builder;
        }

        foreach ($this->filters as $filter) {
            /** @var FilterableType|TypeFactory $type */
            $type = $this->rows[$filter['field']];

            if ($type instanceof TypeFactory) {
                $this->customOperators = $type->getOperators();
                $type = $type->filterableType;
            }

            if (!Operator::is($type, $filter['operator'])) {
                throw new OperatorNotValid($filter['operator'], $type->name);
            }

            $this->matchCustomOperators($filter['operator']);

            match ($type) {
                FilterableType::TEXT => $this->handleText($filter['field'], $filter['operator'], $filter['value']),
                FilterableType::NUMBER => $this->handleNumber($filter['field'], $filter['operator'], $filter['value']),
                FilterableType::DATE => $this->handleDate($filter['field'], $filter['operator'], $filter['value']),
                FilterableType::BOOLEAN => $this->handleBoolean($filter['field'], $filter['operator'], $filter['value']),
            };
        }

        return $this->builder;
    }
}
