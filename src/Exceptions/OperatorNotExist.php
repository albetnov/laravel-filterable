<?php

namespace Albet\LaravelFilterable\Exceptions;

class OperatorNotExist extends \Exception
{
    public function __construct(readonly string $selectedOperator)
    {
        parent::__construct("$this->selectedOperator operator not available");
    }
}
