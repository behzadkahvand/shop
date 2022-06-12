<?php

namespace App\Service\Order\OrderStatus\Exceptions;

use App\Service\ExceptionHandler\ReportableThrowableInterface;
use Symfony\Component\HttpFoundation\Response;

class OrderStatusException extends \Exception implements ReportableThrowableInterface
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    public function shouldReport(): bool
    {
        return false;
    }
}
