<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderShipment;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;

final class NewOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function waitingForSupply(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::WAITING_FOR_SUPPLY);

        $this->changeSellerOrderItemsStatus($orderShipment, SellerOrderItemStatus::WAITING_FOR_SEND);
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::NEW === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::WAITING_FOR_SUPPLY,
            OrderShipmentStatus::CANCELED,
        ];
    }
}
