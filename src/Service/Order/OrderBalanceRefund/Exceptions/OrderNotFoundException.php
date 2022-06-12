<?php

namespace App\Service\Order\OrderBalanceRefund\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class OrderNotFoundException extends OrderBalanceRefundException
{
    protected $code = Response::HTTP_NOT_FOUND;

    protected $message = 'Order is not found for order balance refund action!';
}
