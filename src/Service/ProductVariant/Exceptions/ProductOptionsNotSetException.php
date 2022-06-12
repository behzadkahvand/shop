<?php

namespace App\Service\ProductVariant\Exceptions;

class ProductOptionsNotSetException extends ProductVariantException
{
    protected $message = 'Product options not set!';
}
