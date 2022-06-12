<?php

namespace App\Service\Order\OrderBalanceRefund\Exceptions;

use App\Service\ExceptionHandler\ReportableThrowableInterface;
use Exception;

class OrderBalanceRefundException extends Exception implements ReportableThrowableInterface
{
    public function shouldReport(): bool
    {
        return false;
    }
}
