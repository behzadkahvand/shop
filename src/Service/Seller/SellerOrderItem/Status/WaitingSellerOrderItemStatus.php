<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;

/**
 * Class WaitingSellerOrderItemStatus
 */
final class WaitingSellerOrderItemStatus extends AbstractSellerOrderItemStatus
{
    /**
     * @inheritDoc
     */
    protected function getName(): string
    {
        return SellerOrderItemStatus::WAITING;
    }
}
