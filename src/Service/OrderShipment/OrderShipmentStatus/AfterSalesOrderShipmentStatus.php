<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderShipment;

class AfterSalesOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function waitingForSupply(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::WAITING_FOR_SUPPLY);

        $this->changeSellerOrderItemsStatus($orderShipment, SellerOrderItemStatus::WAITING_FOR_SEND);
    }

    public function waitingForSend(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::WAITING_FOR_SEND);
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::AFTER_SALES === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::WAITING_FOR_SUPPLY,
            OrderShipmentStatus::WAITING_FOR_SEND,
            OrderShipmentStatus::CANCELED,
        ];
    }
}
