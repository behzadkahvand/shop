<?php

namespace App\Events\Order;

use App\Entity\Order;
use Symfony\Contracts\EventDispatcher\Event;

final class OrderRegisteredEvent extends Event
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
