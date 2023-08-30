<?php

namespace Albet\LaravelFilterable\Exceptions;

class OperatorNotValid extends \Exception
{
    public function __construct(readonly string $selectedOperator, readonly string $type)
    {
        parent::__construct("$this->selectedOperator is not valid for $this->type");
    }
}
