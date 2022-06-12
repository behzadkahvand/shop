<?php

namespace App\Service\Condition\Exceptions;

class MaxPurchasePerOrderExceededException extends ConditionException
{
    protected $message = 'Max purchase per order exceeded!';
}
