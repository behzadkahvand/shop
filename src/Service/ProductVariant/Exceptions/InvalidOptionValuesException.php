<?php

namespace App\Service\ProductVariant\Exceptions;

class InvalidOptionValuesException extends ProductVariantException
{
    protected $message = 'Selected option values is invalid!';
}
