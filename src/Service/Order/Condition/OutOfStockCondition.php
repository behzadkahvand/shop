<?php

namespace App\Service\Order\Condition;

use App\Entity\Order;
use App\Service\Condition\OutOfStockCondition as BaseOutOfStockCondition;

class OutOfStockCondition implements OrderConditionInterface
{
    protected BaseOutOfStockCondition $condition;

    public function __construct(BaseOutOfStockCondition $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @throws \App\Service\Condition\Exceptions\OutOfStockException
     */
    public function apply(Order $order): void
    {
        foreach ($order->getOrderItems() as $orderItem) {
            $this->condition->apply($orderItem->getInventory(), $orderItem->getQuantity());
        }
    }
}
