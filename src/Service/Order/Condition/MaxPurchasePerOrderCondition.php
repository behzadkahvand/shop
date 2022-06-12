<?php

namespace App\Service\Order\Condition;

use App\Entity\Order;
use App\Service\Condition\MaxPurchasePerOrderCondition as BaseMaxPurchasePerOrderCondition;

class MaxPurchasePerOrderCondition implements OrderConditionInterface
{
    protected BaseMaxPurchasePerOrderCondition $condition;

    public function __construct(BaseMaxPurchasePerOrderCondition $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @throws \App\Service\Condition\Exceptions\MaxPurchasePerOrderExceededException
     */
    public function apply(Order $order): void
    {
        foreach ($order->getOrderItems() as $orderItem) {
            $this->condition->apply($orderItem->getInventory(), $orderItem->getQuantity());
        }
    }
}
