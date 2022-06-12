<?php

namespace App\Service\ExceptionHandler;

interface ReportableThrowableInterface
{
    public function shouldReport(): bool;
}
