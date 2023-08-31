<?php

namespace Albet\LaravelFilterable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Handler
{
    private readonly string|array $valueString;
    private readonly string $queryOperator;

    public function __construct(
        private readonly Builder $builder,
        private readonly  string $column,
        private readonly  string $operator,
        private readonly string $value
    )
    {
        $this->valueString = Operator::parseOperatorValue($this->operator, $this->value);
        $this->queryOperator = Operator::getQueryOperator($this->operator);
    }

    private function chainWhereQuery(string $column, array $values): void {
        foreach ($values as $value) {
            $this->builder->where($column, $value);
        }
    }

    public function handleText(): void
    {
        match ($this->queryOperator) {
            'in' => $this->builder->whereIn($this->column, $this->valueString),
            'not_in' => $this->builder->whereNotIn($this->column, $this->valueString),
            'have_all' => $this->chainWhereQuery($this->column, $this->valueString),
            default => $this->builder->where($this->column, $this->queryOperator, $this->valueString)
        };
    }

    public function handleNumber(): void
    {
        $fieldValue = Str::contains($this->value, '.') ? (float)$this->value : (int)$this->value;
        $this->builder->where($this->column, $this->queryOperator, $fieldValue);
    }

    public function handleDate(): void
    {
        if (is_array($this->valueString) && count($this->valueString) === 2) {
            $fieldValue = collect($this->valueString)->map(fn(string $item) => Carbon::createFromFormat('n/j/Y', $item))
                ->toArray();

            if ($this->operator === 'in') {
                $this->builder->whereDate($this->column, '>=', $fieldValue[0])
                    ->whereDate($this->column, '<=', $fieldValue[1]);

                return;
            }

            if ($this->operator === 'not_in') {
                $this->builder->whereDate($this->column, '<', $fieldValue[0])
                    ->orWhereDate($this->column, '>', $fieldValue[1]);

                return;
            }
        }

        $fieldValue = Carbon::createFromFormat('n/j/Y', $this->value);

        $this->builder->whereDate($this->column, $this->queryOperator, $fieldValue);
    }

    public function handleBoolean(): void
    {
        if (!in_array($this->value, ['0', '1'])) {
            abort(400, 'Invalid value for boolean filter');
        }

        $this->builder->where($this->column, $this->queryOperator, (bool)$this->value);
    }
}
