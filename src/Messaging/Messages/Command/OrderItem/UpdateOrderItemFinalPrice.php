<?php

namespace App\Messaging\Messages\Command\OrderItem;

class UpdateOrderItemFinalPrice
{
    public function __construct(private int $orderItemId)
    {
    }

    public function getOrderItemId(): int
    {
        return $this->orderItemId;
    }
}
