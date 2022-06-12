<?php

namespace App\Service\Order\UpdateOrderItems\Event;

use App\Entity\OrderItem;

class OrderItemUpdated
{
    private OrderItem $orderItem;

    private int $oldGrandTotal;

    public function __construct(OrderItem $orderItem, int $oldGrandTotal)
    {
        $this->orderItem = $orderItem;
        $this->oldGrandTotal = $oldGrandTotal;
    }

    public function getOrderItem(): OrderItem
    {
        return $this->orderItem;
    }

    public function getOldGrandTotal(): int
    {
        return $this->oldGrandTotal;
    }
}
