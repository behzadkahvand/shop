<?php

namespace App\Service\Product\NotifyMe\Exceptions;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;

class NotifyRequestNotFoundException extends Exception implements
    RenderableThrowableInterface,
    ReportableThrowableInterface
{
    protected $message = 'Notify request not found.';

    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(
            true,
            Response::HTTP_NOT_FOUND,
            $this->getMessage(),
        );
    }

    public function shouldReport(): bool
    {
        return false;
    }
}
