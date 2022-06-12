<?php

namespace App\Service\Order\Condition;

use App\Entity\Order;
use App\Service\Condition\InventoryIsConfirmedCondition as BaseInventoryIsConfirmedCondition;

class InventoryIsConfirmedCondition implements OrderConditionInterface
{
    protected BaseInventoryIsConfirmedCondition $condition;

    public function __construct(BaseInventoryIsConfirmedCondition $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @throws \App\Service\Condition\Exceptions\InventoryIsNotConfirmedException
     */
    public function apply(Order $order): void
    {
        foreach ($order->getOrderItems() as $orderItem) {
            $this->condition->apply($orderItem->getInventory());
        }
    }
}
