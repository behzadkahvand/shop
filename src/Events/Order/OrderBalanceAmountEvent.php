<?php

namespace App\Events\Order;

use Symfony\Contracts\EventDispatcher\Event;

final class OrderBalanceAmountEvent extends Event
{
    private int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
