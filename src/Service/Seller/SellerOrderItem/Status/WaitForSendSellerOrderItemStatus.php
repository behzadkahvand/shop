<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\Seller\SellerOrderItem\Status\Traits\StoragedSellerOrderItemTrait;

/**
 * Class WaitForSendSellerOrderItemStatus
 */
final class WaitForSendSellerOrderItemStatus extends AbstractSellerOrderItemStatus
{
    use StoragedSellerOrderItemTrait;

    /**
     * @inheritDoc
     */
    protected function getName(): string
    {
        return SellerOrderItemStatus::WAITING_FOR_SEND;
    }
}
