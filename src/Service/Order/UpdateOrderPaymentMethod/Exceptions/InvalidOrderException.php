<?php

namespace App\Service\Order\UpdateOrderPaymentMethod\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InvalidOrderException extends UpdatePaymentMethodException
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected $message = 'Order is invalid for updating payment method action!';
}
