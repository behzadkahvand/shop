<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;

/**
 * Class SentToCustomerSellerOrderItemStatus
 */
final class SentToCustomerSellerOrderItemStatus extends AbstractSellerOrderItemStatus
{
    /**
     * @inheritDoc
     */
    protected function getName(): string
    {
        return SellerOrderItemStatus::SENT_TO_CUSTOMER;
    }
}
