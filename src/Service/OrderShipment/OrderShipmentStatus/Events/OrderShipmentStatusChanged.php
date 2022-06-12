<?php

namespace App\Service\OrderShipment\OrderShipmentStatus\Events;

use App\Entity\OrderShipment;
use Symfony\Contracts\EventDispatcher\Event;

final class OrderShipmentStatusChanged extends Event
{
    private OrderShipment $orderShipment;

    private string $oldStatus;

    private string $newStatus;

    public function __construct(OrderShipment $orderShipment, string $oldStatus, string $newStatus)
    {
        $this->orderShipment = $orderShipment;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function getOrderShipment(): OrderShipment
    {
        return $this->orderShipment;
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
