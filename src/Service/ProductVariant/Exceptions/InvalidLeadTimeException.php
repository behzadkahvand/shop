<?php

namespace App\Service\ProductVariant\Exceptions;

class InvalidLeadTimeException extends ProductVariantException
{
    protected $message = 'Lead time has invalid value!';
}
