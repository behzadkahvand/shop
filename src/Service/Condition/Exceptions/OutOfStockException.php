<?php

namespace App\Service\Condition\Exceptions;

class OutOfStockException extends ConditionException
{
    protected $message = 'Item is out of stock!';
}
