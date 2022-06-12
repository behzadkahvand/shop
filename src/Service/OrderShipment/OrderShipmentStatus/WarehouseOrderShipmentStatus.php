<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;

class WarehouseOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function preparing(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::PREPARING);
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::WAREHOUSE === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::PREPARING,
            OrderShipmentStatus::CANCELED,
        ];
    }
}
