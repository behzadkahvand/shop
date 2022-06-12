<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\Seller\SellerOrderItem\Status\Traits\StoragedSellerOrderItemTrait;

/**
 * Class SentBySellerSellerOrderItemStatus
 */
final class SentBySellerSellerOrderItemStatus extends AbstractSellerOrderItemStatus
{
    use StoragedSellerOrderItemTrait;

    /**
     * @inheritDoc
     */
    protected function getName(): string
    {
        return SellerOrderItemStatus::SENT_BY_SELLER;
    }
}
