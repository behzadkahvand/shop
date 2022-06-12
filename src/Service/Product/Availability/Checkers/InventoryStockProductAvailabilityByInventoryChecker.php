<?php

namespace App\Service\Product\Availability\Checkers;

use App\Entity\Inventory;
use App\Service\Condition\Exceptions\OutOfStockException;
use App\Service\Condition\OutOfStockCondition as InventoryStockCondition;

final class InventoryStockProductAvailabilityByInventoryChecker extends AbstractProductAvailabilityByInventoryChecker
{
    private InventoryStockCondition $ofStockCondition;

    public function __construct(InventoryStockCondition $ofStockCondition)
    {
        $this->ofStockCondition = $ofStockCondition;
    }

    public function productShouldBeAvailable(Inventory $inventory): bool
    {
        try {
            $this->ofStockCondition->apply($inventory, 1);

            return true;
        } catch (OutOfStockException $e) {
            return false;
        }
    }
}
