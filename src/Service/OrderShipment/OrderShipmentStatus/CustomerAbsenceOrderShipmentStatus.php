<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;
use App\Service\OrderShipment\OrderShipmentStatus\Traits\DeliveredOrderShipmentTrait;

class CustomerAbsenceOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    use DeliveredOrderShipmentTrait;

    public function waitingForSend(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::WAITING_FOR_SEND);
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::CUSTOMER_ABSENCE === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::WAITING_FOR_SEND,
            OrderShipmentStatus::DELIVERED,
            OrderShipmentStatus::CANCELED,
        ];
    }
}
