<?php

namespace App\Service\Seller\SellerOrderItem\Exceptions;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AllSellerOrderItemsAlreadySentException
 */
final class AllSellerOrderItemsAlreadySentException extends \Exception implements RenderableThrowableInterface, ReportableThrowableInterface
{
    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(true, 422, 'All items already sent.');
    }

    public function shouldReport(): bool
    {
        return false;
    }
}
