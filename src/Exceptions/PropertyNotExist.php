<?php

namespace Albet\LaravelFilterable\Exceptions;

class PropertyNotExist extends \Exception
{
    public function __construct()
    {
        parent::__construct('$rows or getRows() is not exist');
    }
}
