<?php

namespace Albet\LaravelFilterable\Exceptions;

class PropertyNotExist extends \Exception
{
    public function __construct()
    {
        parent::__construct('$filterableColumns or getFilterableColumns() is not exist');
    }
}
