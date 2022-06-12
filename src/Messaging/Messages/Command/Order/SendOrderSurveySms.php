<?php

namespace App\Messaging\Messages\Command\Order;

final class SendOrderSurveySms
{
    public function __construct(private int $orderId)
    {
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
