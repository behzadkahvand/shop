<?php

namespace App\Service\Condition;

use App\Entity\Inventory;
use App\Service\Condition\Exceptions\InventoryIsNotActiveException;

class InventoryIsActiveCondition
{
    public function apply(Inventory $inventory): void
    {
        if (! $inventory->getIsActive()) {
            throw new InventoryIsNotActiveException();
        }
    }
}
