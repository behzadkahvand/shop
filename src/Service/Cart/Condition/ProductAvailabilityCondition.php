<?php

namespace App\Service\Cart\Condition;

use App\Entity\Inventory;
use App\Service\Condition\ProductAvailabilityCondition as BaseProductAvailabilityCondition;

class ProductAvailabilityCondition implements CartConditionInterface
{
    protected BaseProductAvailabilityCondition $condition;

    public function __construct(BaseProductAvailabilityCondition $condition)
    {
        $this->condition = $condition;
    }

    /**
     *
     * @throws \App\Service\Condition\Exceptions\ProductIsNotActiveException
     */
    public function apply(Inventory $inventory, int $quantity): void
    {
        $this->condition->apply($inventory);
    }
}
