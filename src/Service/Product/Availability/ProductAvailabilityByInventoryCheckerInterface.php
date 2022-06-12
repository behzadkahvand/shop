<?php

namespace App\Service\Product\Availability;

use App\Entity\Inventory;

/**
 * Interface ProductAvailabilityByInventoryCheckerInterface
 */
interface ProductAvailabilityByInventoryCheckerInterface
{
    /**
     * @param Inventory $inventory
     *
     * @return bool
     */
    public function productShouldBeAvailable(Inventory $inventory): bool;

    /**
     * @param Inventory $inventory
     *
     * @return bool
     */
    public function productShouldBeUnavailable(Inventory $inventory): bool;
}
