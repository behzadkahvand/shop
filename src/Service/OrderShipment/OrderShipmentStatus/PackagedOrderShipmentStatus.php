<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;

class PackagedOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function prepared(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::PREPARED)
                      ->setPackagedCount(0);
    }

    public function waitingForSend(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::WAITING_FOR_SEND);
    }

    public function packaged(OrderShipment $orderShipment): void
    {
        $orderShipment->increasePackagedCount();
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::PREPARED,
            OrderShipmentStatus::PACKAGED,
            OrderShipmentStatus::WAITING_FOR_SEND,
            OrderShipmentStatus::CANCELED,
        ];
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::PACKAGED === $status;
    }
}
