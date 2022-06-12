<?php

namespace App\Service\Condition;

use App\Entity\Inventory;
use App\Service\Condition\Exceptions\InventoryIsNotConfirmedException;

class InventoryIsConfirmedCondition
{
    public function apply(Inventory $inventory): void
    {
        if (! $inventory->isConfirmed()) {
            throw new InventoryIsNotConfirmedException();
        }
    }
}
