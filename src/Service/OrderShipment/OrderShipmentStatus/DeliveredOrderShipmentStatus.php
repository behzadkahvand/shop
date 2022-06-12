<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;

class DeliveredOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function afterSales(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::AFTER_SALES);
    }

    public function canceled(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::DELIVERED === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::AFTER_SALES,
        ];
    }
}
