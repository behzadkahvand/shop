<?php

namespace App\Exceptions\Discount;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class InventoryDiscountRuleViolationException extends Exception implements ReportableThrowableInterface, RenderableThrowableInterface
{
    protected $code = Response::HTTP_BAD_REQUEST;

    public function shouldReport(): bool
    {
        return false;
    }

    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(true, $this->getCode(), $this->getMessage());
    }
}
