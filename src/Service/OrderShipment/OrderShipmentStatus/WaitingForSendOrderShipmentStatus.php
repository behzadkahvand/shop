<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderShipment;

class WaitingForSendOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function preparing(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::PREPARING)
                      ->increasePackagedCount(0);

        $this->changeSellerOrderItemsStatus($orderShipment, SellerOrderItemStatus::FULFILLING);
    }

    public function sent(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::SENT);

        $this->changeSellerOrderItemsStatus($orderShipment, SellerOrderItemStatus::SENT_TO_CUSTOMER);
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::WAITING_FOR_SEND === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::PREPARING,
            OrderShipmentStatus::SENT,
            OrderShipmentStatus::CANCELED,
        ];
    }
}
