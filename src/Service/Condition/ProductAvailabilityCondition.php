<?php

namespace App\Service\Condition;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Service\Condition\Exceptions\ProductIsNotActiveException;

class ProductAvailabilityCondition
{
    public function apply(Inventory $inventory): void
    {
        if (! $inventory->getProductIsActive()) {
            throw new ProductIsNotActiveException();
        }

        if ($inventory->getProductStatus() !== ProductStatusDictionary::CONFIRMED) {
            throw new ProductIsNotActiveException();
        }
    }
}
