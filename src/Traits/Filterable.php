<?php

namespace Albet\LaravelFilterable\Traits;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Operator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

trait Filterable
{
    private function getFilterable(): array
    {
        if (method_exists($this, 'getRows')) {
            return $this->getRows();
        }

        if (property_exists($this, 'rows')) {
            return $this->rows;
        }

        throw new \InvalidArgumentException('getRows() or $rows is not exist');
    }

    private function constructWhere(Builder $builder, string $column, array $values): Builder
    {
        foreach ($values as $value) {
            $builder->where($column, $value);
        }

        return $builder;
    }

    private function handleText(Builder $builder, string $column, string $operator, string $text): Builder
    {
        if (! Operator::isTextOperator($operator)) {
            abort(400, 'Invalid operator for text type.');
        }

        $queryOperator = Operator::getQueryOperator($operator);
        $fieldValue = Operator::parseOperatorValue($operator, $text);

        return match ($operator) {
            'in' => $builder->whereIn($column, $fieldValue),
            'not_in' => $builder->whereNotIn($column, $fieldValue),
            'have_all' => $this->constructWhere($builder, $column, $fieldValue),
            default => $builder->where($column, $queryOperator, $fieldValue)
        };
    }

    private function handleNumber(Builder $builder, string $column, string $operator, string $number): Builder
    {
        if (! Operator::isNumberOperator($operator)) {
            abort(400, 'Invalid operator for number type.');
        }

        $operator = Operator::getQueryOperator($operator);
        $fieldValue = Str::contains($number, '.') ? (float) $number : (int) $number;

        return $builder->where($column, $operator, $fieldValue);
    }

    private function handleDate(Builder $builder, string $column, string $operator, string $date): Builder
    {
        if (! Operator::isDateOperator($operator)) {
            abort(400, 'Invalid operator for date type.');
        }

        $queryOperator = Operator::getQueryOperator($operator);
        $fieldValue = Operator::parseOperatorValue($operator, $date);

        if (is_array($fieldValue) && count($fieldValue) === 2) {
            $fieldValue = collect($fieldValue)->map(fn (string $item) => Carbon::createFromFormat('n/j/Y', $item))
                ->toArray();

            if ($operator === 'in') {
                return $builder->whereDate($column, '>=', $fieldValue[0])->whereDate($column, '<=', $fieldValue[1]);
            }

            if ($operator === 'not_in') {
                return $builder->whereDate($column, '<', $fieldValue[0])->orWhereDate($column, '>', $fieldValue[1]);
            }
        }

        $fieldValue = Carbon::createFromFormat('n/j/Y', $date);

        return $builder->whereDate($column, $queryOperator, $fieldValue);
    }

    private function handleBoolean(Builder $builder, string $column, string $operator, string $bool): Builder
    {
        if (! Operator::isBooleanOperator($operator)) {
            abort(400, 'Invalid operator for boolean type');
        }

        if (! in_array($bool, ['0', '1'])) {
            abort(400, 'Invalid value for boolean filter');
        }

        $operator = Operator::getQueryOperator($operator);

        return $builder->where($column, $operator, (bool) $bool);
    }

    private function filterBy(Builder $builder, string $column, string $operator, string $value): Builder
    {
        $type = $this->getFilterable()[$column];

        return match ($type) {
            FilterableType::TEXT => $this->handleText($builder, $column, $operator, $value),
            FilterableType::NUMBER => $this->handleNumber($builder, $column, $operator, $value),
            FilterableType::DATE => $this->handleDate($builder, $column, $operator, $value),
            FilterableType::BOOLEAN => $this->handleBoolean($builder, $column, $operator, $value),
        };
    }

    public function scopeFilter(Builder $query)
    {
        $request = request();

        $request->validate([
            'filters.*.field' => ['required', 'string', Rule::in(collect($this->getFilterable())->keys()->toArray())],
            'filters.*.operator' => ['required', Rule::in(Operator::getAllOperators())],
            'filters.*.value' => 'required',
        ]);

        foreach ($request->get('filters', []) as $filter) {
            $this->filterBy($query, $filter['field'], $filter['operator'], $filter['value']);
        }

        return $query;
    }
}
