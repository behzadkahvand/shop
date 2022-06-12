<?php

namespace App\Service\Product\Availability\Checkers;

use App\Entity\Inventory;
use App\Service\Condition\Exceptions\InventoryIsNotConfirmedException;
use App\Service\Condition\InventoryIsConfirmedCondition;

class InventoryConfirmedProductAvailabilityByInventoryChecker extends AbstractProductAvailabilityByInventoryChecker
{
    protected InventoryIsConfirmedCondition $inventoryIsConfirmedCondition;

    public function __construct(InventoryIsConfirmedCondition $inventoryIsConfirmedCondition)
    {
        $this->inventoryIsConfirmedCondition = $inventoryIsConfirmedCondition;
    }

    public function productShouldBeAvailable(Inventory $inventory): bool
    {
        try {
            $this->inventoryIsConfirmedCondition->apply($inventory);

            return true;
        } catch (InventoryIsNotConfirmedException $e) {
            return false;
        }
    }
}
