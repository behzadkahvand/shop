<?php

namespace App\Service\ProductVariant\Exceptions;

use App\Service\ExceptionHandler\ReportableThrowableInterface;

class InventoryNotFoundException extends \Exception implements ReportableThrowableInterface
{
    protected $message = "Inventory not found !";

    public function shouldReport(): bool
    {
        return true;
    }
}
