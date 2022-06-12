<?php

namespace App\Service\Order\UpdateOrderItems\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InvalidOrderItemIdException extends UpdateOrderItemsException
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected $message = 'Order item id is invalid!';
}
