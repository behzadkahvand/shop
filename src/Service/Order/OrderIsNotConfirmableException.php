<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Service\ExceptionHandler\RenderableThrowableInterface;
use App\Service\ExceptionHandler\ReportableThrowableInterface;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class OrderIsNotConfirmableException
 */
final class OrderIsNotConfirmableException extends \RuntimeException implements RenderableThrowableInterface, ReportableThrowableInterface
{
    public function __construct(Order $order)
    {
        parent::__construct("Order with identifier {$order->getIdentifier()} is not confirmable.");
    }

    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata
    {
        return new ThrowableMetadata(false, 500, $this->getMessage());
    }

    public function shouldReport(): bool
    {
        return true;
    }
}
