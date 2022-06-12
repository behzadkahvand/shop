<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;

/**
 * Class MissedSellerOrderItemStatus
 */
final class MissedSellerOrderItemStatus extends AbstractSellerOrderItemStatus
{
    /**
     * @inheritDoc
     */
    protected function getName(): string
    {
        return SellerOrderItemStatus::MISSED;
    }
}
