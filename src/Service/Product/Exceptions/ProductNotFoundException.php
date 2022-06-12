<?php

namespace App\Service\Product\Exceptions;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductNotFoundException extends Exception implements RenderableThrowableInterface, ReportableThrowableInterface
{
    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(
            true,
            404,
            'Selected product not found!',
        );
    }

    public function shouldReport(): bool
    {
        return false;
    }
}
