<?php

namespace App\Service\Order\OrderStatus\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InvalidOrderStatusMethodException extends OrderStatusException
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;

    protected $message = 'Order status method is invalid!';
}
