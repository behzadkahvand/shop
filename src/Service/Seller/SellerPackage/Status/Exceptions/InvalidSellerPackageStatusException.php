<?php

namespace App\Service\Seller\SellerPackage\Status\Exceptions;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class InvalidSellerPackageStatusException
 */
final class InvalidSellerPackageStatusException extends Exception implements RenderableThrowableInterface, ReportableThrowableInterface
{
    public function __construct(string $status)
    {
        parent::__construct("{$status} is not a valid seller package status.");
    }

    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(true, Response::HTTP_UNPROCESSABLE_ENTITY, $this->getMessage());
    }

    public function shouldReport(): bool
    {
        return false;
    }
}
