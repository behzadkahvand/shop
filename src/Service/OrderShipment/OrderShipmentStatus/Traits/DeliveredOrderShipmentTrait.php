<?php

namespace App\Service\OrderShipment\OrderShipmentStatus\Traits;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderShipment;

trait DeliveredOrderShipmentTrait
{
    public function delivered(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::DELIVERED);

        $this->changeSellerOrderItemsStatus($orderShipment, SellerOrderItemStatus::DELIVERED);

        $order = $orderShipment->getOrder();

        if ($this->orderShouldBeDelivered($order)) {
            $this->changeOrderStatus($order, OrderStatus::DELIVERED);
        }
    }
}
