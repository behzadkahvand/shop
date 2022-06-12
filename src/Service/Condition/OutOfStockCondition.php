<?php

namespace App\Service\Condition;

use App\Entity\Inventory;
use App\Service\Condition\Exceptions\OutOfStockException;

class OutOfStockCondition
{
    public function apply(Inventory $inventory, int $quantity): void
    {
        if ($inventory->getSellerStock() < $quantity) {
            throw new OutOfStockException();
        }
    }
}
