<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderShipment;

class PreparingOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function waitingForSupply(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::WAITING_FOR_SUPPLY);

        $this->changeSellerOrderItemsStatus($orderShipment, SellerOrderItemStatus::WAITING_FOR_SEND);
    }

    public function prepared(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::PREPARED);
    }

    public function warehouse(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::WAREHOUSE);
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::PREPARING === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::WAITING_FOR_SUPPLY,
            OrderShipmentStatus::PREPARED,
            OrderShipmentStatus::WAREHOUSE,
            OrderShipmentStatus::CANCELED,
        ];
    }
}
