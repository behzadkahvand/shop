<?php

namespace App\Service\Order\OrderStatus\Exceptions;

class InvalidOrderStatusTransitionException extends OrderStatusException
{
    protected $message = 'Order status transition is invalid!';
}
