<?php

namespace App\Service\Order\Condition;

use App\Entity\Order;
use App\Service\Condition\InventoryIsActiveCondition as BaseInventoryIsActiveCondition;

class InventoryIsActiveCondition implements OrderConditionInterface
{
    protected BaseInventoryIsActiveCondition $condition;

    public function __construct(BaseInventoryIsActiveCondition $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @throws \App\Service\Condition\Exceptions\InventoryIsNotActiveException
     */
    public function apply(Order $order): void
    {
        foreach ($order->getOrderItems() as $orderItem) {
            $this->condition->apply($orderItem->getInventory());
        }
    }
}
