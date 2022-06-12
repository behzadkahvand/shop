<?php

namespace App\Service\Order\UpdateOrderPaymentMethod\Exceptions;

use App\Service\ExceptionHandler\ReportableThrowableInterface;
use Exception;

class UpdatePaymentMethodException extends Exception implements ReportableThrowableInterface
{
    public function shouldReport(): bool
    {
        return false;
    }
}
