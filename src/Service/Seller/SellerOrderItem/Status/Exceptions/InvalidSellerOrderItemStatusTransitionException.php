<?php

namespace App\Service\Seller\SellerOrderItem\Status\Exceptions;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class InvalidSellerOrderItemStatusTransitionException
 */
final class InvalidSellerOrderItemStatusTransitionException extends \Exception implements RenderableThrowableInterface, ReportableThrowableInterface
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    /**
     * InvalidSellerOrderItemStatusTransitionException constructor.
     */
    public function __construct(string $fromStatus, string $toStatus)
    {
        parent::__construct("Seller order item status transition from {$fromStatus} to {$toStatus} is invalid!");
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(true, $this->code, $this->getMessage());
    }

    public function shouldReport(): bool
    {
        return false;
    }
}
