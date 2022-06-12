<?php

namespace App\Service\Seller\SellerOrderItem\Exceptions;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class InvalidSellerPackageDescription
 */
final class InvalidSellerPackageDescription extends \InvalidArgumentException implements RenderableThrowableInterface, ReportableThrowableInterface
{
    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(true, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid description for package');
    }

    public function shouldReport(): bool
    {
        return false;
    }
}
