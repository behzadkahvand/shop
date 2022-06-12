<?php

namespace App\Service\Order\Condition;

use App\Entity\Order;
use App\Service\Order\Condition\Exceptions\OrderItemInventoryPriceHasBeenUpdatedException;

class PriceIntegrityCondition implements OrderConditionInterface
{
    public function apply(Order $order): void
    {
        foreach ($order->getOrderItems() as $orderItem) {
            if ($orderItem->priceHasBeenUpdated()) {
                throw new OrderItemInventoryPriceHasBeenUpdatedException();
            }
        }
    }
}
