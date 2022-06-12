<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderShipment;
use App\Service\OrderShipment\OrderShipmentStatus\Traits\DeliveredOrderShipmentTrait;

class SentOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    use DeliveredOrderShipmentTrait;

    public function returning(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::RETURNING);
    }

    public function thirdPartyLogistics(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::THIRD_PARTY_LOGISTICS);
    }

    public function customerAbsence(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::CUSTOMER_ABSENCE);
    }

    public function canceledByCustomer(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::CANCELED_BY_CUSTOMER);

        $this->changeSellerOrderItemsStatus($orderShipment, SellerOrderItemStatus::CANCELED_BY_USER);

        $this->recalculateOrderDocument($orderShipment->getOrder());
    }

    public function noSend(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::NO_SEND);
    }

    public function support(string $status): bool
    {
        return OrderShipmentStatus::SENT === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::DELIVERED,
            OrderShipmentStatus::RETURNING,
            OrderShipmentStatus::THIRD_PARTY_LOGISTICS,
            OrderShipmentStatus::CANCELED,
            OrderShipmentStatus::CUSTOMER_ABSENCE,
            OrderShipmentStatus::CANCELED_BY_CUSTOMER,
            OrderShipmentStatus::NO_SEND,
        ];
    }
}
