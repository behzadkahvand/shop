<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;

/**
 * Class ReturnedSellerOrderItemStatus
 */
final class ReturnedSellerOrderItemStatus extends AbstractSellerOrderItemStatus
{
    /**
     * @inheritDoc
     */
    protected function getName(): string
    {
        return SellerOrderItemStatus::RETURNED;
    }
}
