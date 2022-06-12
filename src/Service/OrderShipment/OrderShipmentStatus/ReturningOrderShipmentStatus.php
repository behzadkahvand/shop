<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;

class ReturningOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function returned(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::RETURNED);
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::RETURNING === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::RETURNED,
            OrderShipmentStatus::CANCELED,
        ];
    }
}
