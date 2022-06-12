<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\Seller\SellerOrderItem\Status\Traits\StoragedSellerOrderItemTrait;

/**
 * Class ReceivedSellerOrderItemStatus
 */
final class ReceivedSellerOrderItemStatus extends AbstractSellerOrderItemStatus
{
    use StoragedSellerOrderItemTrait;

    /**
     * @inheritDoc
     */
    protected function getName(): string
    {
        return SellerOrderItemStatus::RECEIVED;
    }
}
