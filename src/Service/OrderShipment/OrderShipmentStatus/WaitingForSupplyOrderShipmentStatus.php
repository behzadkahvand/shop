<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderShipment;

class WaitingForSupplyOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function preparing(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::PREPARING);

        $this->changeSellerOrderItemsStatus($orderShipment, SellerOrderItemStatus::FULFILLING);
    }

    public function warehouse(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::WAREHOUSE);
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::WAITING_FOR_SUPPLY === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::PREPARING,
            OrderShipmentStatus::CANCELED,
            OrderShipmentStatus::WAREHOUSE,
        ];
    }
}
