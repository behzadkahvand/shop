<?php

namespace App\Service\Order\Condition\Exceptions;

use App\Service\Condition\Exceptions\ConditionException;

class OrderItemInventoryPriceHasBeenUpdatedException extends ConditionException
{
    protected $message = 'Order item price has updated!';
}
