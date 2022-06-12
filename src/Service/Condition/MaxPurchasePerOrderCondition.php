<?php

namespace App\Service\Condition;

use App\Entity\Inventory;
use App\Service\Condition\Exceptions\MaxPurchasePerOrderExceededException;

class MaxPurchasePerOrderCondition
{
    public function apply(Inventory $inventory, int $quantity): void
    {
        if ($quantity < 0 || $inventory->getMaxPurchasePerOrder() < $quantity) {
            throw new MaxPurchasePerOrderExceededException();
        }
    }
}
