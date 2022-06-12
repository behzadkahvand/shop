<?php

namespace App\Messaging\Messages\Command\Order;

final class SendOrderAffiliatorPurchaseRequest
{
    public function __construct(protected int $orderId)
    {
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
