<?php

namespace App\Service\Order\OrderStatus\Events;

use App\Entity\Order;
use Symfony\Contracts\EventDispatcher\Event;

final class OrderStatusChanged extends Event
{
    private Order $order;

    private string $oldStatus;

    private string $newStatus;

    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getOldStatus(): string
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }
}
