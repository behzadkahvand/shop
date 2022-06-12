<?php

namespace App\Service\Seller\SellerPackage\Status\Exceptions;

use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class InvalidSellerOrderItemStatusTransitionException
 */
final class CouldNotFindSellerPackageStatusException extends \Exception implements RenderableThrowableInterface, ReportableThrowableInterface
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    /**
     * InvalidSellerOrderItemStatusTransitionException constructor.
     */
    public function __construct()
    {
        parent::__construct('Could not find a appropriate status for seller package!');
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
