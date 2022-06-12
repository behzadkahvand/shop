<?php

namespace App\Exceptions\Product\Import;

class DuplicateProductException extends ProductImportException
{
    protected $message = 'Product already exist';
}
