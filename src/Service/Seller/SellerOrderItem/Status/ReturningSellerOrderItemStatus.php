<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;

/**
 * Class ReturningSellerOrderItemStatus
 */
final class ReturningSellerOrderItemStatus extends AbstractSellerOrderItemStatus
{
    /**
     * @inheritDoc
     */
    protected function getName(): string
    {
        return SellerOrderItemStatus::RETURNING;
    }
}
