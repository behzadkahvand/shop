<?php

namespace App\Service\Product\NotifyMe\Exceptions;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;

class NotifyRequestAlreadyExistsException extends Exception implements
    RenderableThrowableInterface,
    ReportableThrowableInterface
{
    protected $message = 'You already have added this product.';

    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(
            true,
            Response::HTTP_CONFLICT,
            $this->getMessage(),
        );
    }

    public function shouldReport(): bool
    {
        return false;
    }
}
