<?php

namespace App\Service\Order\Condition;

use App\Entity\Order;
use App\Service\Condition\ProductAvailabilityCondition as BaseProductAvailabilityCondition;

class ProductAvailabilityCondition implements OrderConditionInterface
{
    protected BaseProductAvailabilityCondition $condition;

    public function __construct(BaseProductAvailabilityCondition $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @throws \App\Service\Condition\Exceptions\ProductIsNotActiveException
     */
    public function apply(Order $order): void
    {
        foreach ($order->getOrderItems() as $orderItem) {
            $this->condition->apply($orderItem->getInventory());
        }
    }
}
