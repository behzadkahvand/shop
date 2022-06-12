<?php

namespace App\Service\Order\UpdateOrderItems\Exceptions;

use App\Service\ExceptionHandler\ReportableThrowableInterface;
use Exception;

class UpdateOrderItemsException extends Exception implements ReportableThrowableInterface
{
    public function shouldReport(): bool
    {
        return false;
    }
}
