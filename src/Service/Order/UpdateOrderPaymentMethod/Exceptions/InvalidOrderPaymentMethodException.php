<?php

namespace App\Service\Order\UpdateOrderPaymentMethod\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InvalidOrderPaymentMethodException extends UpdatePaymentMethodException
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected $message = 'Order payment method is invalid!';
}
