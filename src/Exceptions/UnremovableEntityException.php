<?php

namespace App\Exceptions;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class UnremovableEntityException extends Exception implements ReportableThrowableInterface, RenderableThrowableInterface
{
    public function shouldReport(): bool
    {
        return false;
    }

    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(true, $this->getCode(), $this->getMessage());
    }
}
