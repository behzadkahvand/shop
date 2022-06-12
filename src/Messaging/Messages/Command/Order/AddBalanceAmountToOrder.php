<?php

namespace App\Messaging\Messages\Command\Order;

class AddBalanceAmountToOrder
{
    public function __construct(protected int $orderId)
    {
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
