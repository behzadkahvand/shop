<?php

namespace App\Service\Product\Availability\Checkers;

use App\Entity\Inventory;
use App\Service\Product\Availability\ProductAvailabilityByInventoryCheckerInterface;

abstract class AbstractProductAvailabilityByInventoryChecker implements ProductAvailabilityByInventoryCheckerInterface
{
    public function productShouldBeUnavailable(Inventory $inventory): bool
    {
        return ! $this->productShouldBeAvailable($inventory);
    }
}
