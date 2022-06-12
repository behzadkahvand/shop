<?php

namespace App\Service\Cart\Condition;

use App\Entity\Inventory;
use App\Service\Condition\InventoryIsConfirmedCondition as BaseInventoryIsConfirmedCondition;

class InventoryIsConfirmedCondition implements CartConditionInterface
{
    protected BaseInventoryIsConfirmedCondition $condition;

    public function __construct(BaseInventoryIsConfirmedCondition $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @throws \App\Service\Condition\Exceptions\InventoryIsNotConfirmedException
     */
    public function apply(Inventory $inventory, int $quantity): void
    {
        $this->condition->apply($inventory);
    }
}
