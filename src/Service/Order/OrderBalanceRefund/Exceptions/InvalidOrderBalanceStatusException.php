<?php

namespace App\Service\Order\OrderBalanceRefund\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InvalidOrderBalanceStatusException extends OrderBalanceRefundException
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected $message = 'Order balance status is invalid for order balance refund action!';
}
