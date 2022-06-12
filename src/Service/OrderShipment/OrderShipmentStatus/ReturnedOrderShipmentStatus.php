<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;

class ReturnedOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function afterSales(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::AFTER_SALES);
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::RETURNED === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::AFTER_SALES,
            OrderShipmentStatus::CANCELED,
        ];
    }
}
