<?php

namespace App\Service\Order\UpdateOrderItems\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InvalidOrderException extends UpdateOrderItemsException
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected $message = 'Order is invalid for updating order items action!';
}
