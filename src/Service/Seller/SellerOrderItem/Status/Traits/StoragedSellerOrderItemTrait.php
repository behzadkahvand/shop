<?php

namespace App\Service\Seller\SellerOrderItem\Status\Traits;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\SellerOrderItem;

trait StoragedSellerOrderItemTrait
{
    /**
     * @inheritDoc
     */
    public function storaged(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::STORAGED);

        $shipment = $sellerOrderItem->getOrderItem()->getOrderShipment();

        if ($this->isShipmentFullyStoraged($shipment)) {
            $this->orderShipmentStatusService->change($shipment, OrderShipmentStatus::WAREHOUSE);
        }
    }
}
