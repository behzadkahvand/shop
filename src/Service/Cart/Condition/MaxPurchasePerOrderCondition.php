<?php

namespace App\Service\Cart\Condition;

use App\Entity\Inventory;
use App\Service\Condition\MaxPurchasePerOrderCondition as BaseMaxPurchasePerOrderCondition;

class MaxPurchasePerOrderCondition implements CartConditionInterface
{
    protected BaseMaxPurchasePerOrderCondition $condition;

    public function __construct(BaseMaxPurchasePerOrderCondition $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @throws \App\Service\Condition\Exceptions\MaxPurchasePerOrderExceededException
     */
    public function apply(Inventory $inventory, int $quantity): void
    {
        $this->condition->apply($inventory, $quantity);
    }
}
