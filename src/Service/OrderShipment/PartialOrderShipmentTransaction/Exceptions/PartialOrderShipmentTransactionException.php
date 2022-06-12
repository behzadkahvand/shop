<?php

namespace App\Service\OrderShipment\PartialOrderShipmentTransaction\Exceptions;

use App\Service\ExceptionHandler\ReportableThrowableInterface;
use Exception;

class PartialOrderShipmentTransactionException extends Exception implements ReportableThrowableInterface
{
    public function shouldReport(): bool
    {
        return false;
    }
}
