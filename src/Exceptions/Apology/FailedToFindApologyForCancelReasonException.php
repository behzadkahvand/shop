<?php

namespace App\Exceptions\Apology;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class FailedToFindApologyForCancelReasonException extends Exception implements ReportableThrowableInterface, RenderableThrowableInterface
{
    protected $message = 'Failed to find apology for cancel reason';

    public function shouldReport(): bool
    {
        return false;
    }

    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(true, $this->getCode(), $this->getMessage());
    }
}
