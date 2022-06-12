<?php

namespace App\Service\Cart\Condition;

use App\Entity\Inventory;
use App\Service\Condition\OutOfStockCondition as BaseOutOfStockCondition;

class OutOfStockCondition implements CartConditionInterface
{
    protected BaseOutOfStockCondition $condition;

    public function __construct(BaseOutOfStockCondition $condition)
    {
        $this->condition = $condition;
    }

    /**
     *
     * @throws \App\Service\Condition\Exceptions\OutOfStockException
     */
    public function apply(Inventory $inventory, int $quantity): void
    {
        $this->condition->apply($inventory, $quantity);
    }
}
