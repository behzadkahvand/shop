<?php

namespace App\Service\Condition\Exceptions;

use App\Service\ExceptionHandler\ReportableThrowableInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class ConditionException extends Exception implements ReportableThrowableInterface
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    public function shouldReport(): bool
    {
        return false;
    }
}
