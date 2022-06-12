<?php

namespace App\Service\Cart\Condition;

use App\Entity\Inventory;
use App\Service\Condition\InventoryIsActiveCondition as BaseInventoryIsActiveCondition;

class InventoryIsActiveCondition implements CartConditionInterface
{
    protected BaseInventoryIsActiveCondition $condition;

    public function __construct(BaseInventoryIsActiveCondition $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @throws \App\Service\Condition\Exceptions\InventoryIsNotActiveException
     */
    public function apply(Inventory $inventory, int $quantity): void
    {
        $this->condition->apply($inventory);
    }
}
