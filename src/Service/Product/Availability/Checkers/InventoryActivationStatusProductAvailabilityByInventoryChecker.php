<?php

namespace App\Service\Product\Availability\Checkers;

use App\Entity\Inventory;
use App\Service\Condition\Exceptions\InventoryIsNotActiveException;
use App\Service\Condition\InventoryIsActiveCondition;

final class InventoryActivationStatusProductAvailabilityByInventoryChecker extends AbstractProductAvailabilityByInventoryChecker
{
    private InventoryIsActiveCondition $availabilityCondition;

    public function __construct(InventoryIsActiveCondition $availabilityCondition)
    {
        $this->availabilityCondition = $availabilityCondition;
    }

    public function productShouldBeAvailable(Inventory $inventory): bool
    {
        try {
            $this->availabilityCondition->apply($inventory);

            return true;
        } catch (InventoryIsNotActiveException $e) {
            return false;
        }
    }
}
