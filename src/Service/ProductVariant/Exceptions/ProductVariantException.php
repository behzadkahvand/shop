<?php

namespace App\Service\ProductVariant\Exceptions;

use App\Service\ExceptionHandler\ReportableThrowableInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class ProductVariantException extends Exception implements ReportableThrowableInterface
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    public function shouldReport(): bool
    {
        return false;
    }
}
