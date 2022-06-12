<?php

namespace App\Service\Order\UpdateOrderPaymentMethod\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class OfflinePaymentMethodException extends UpdatePaymentMethodException
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected $message = 'Offline payment method is invalid because order is paid or order has several shipments!';
}
