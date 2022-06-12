<?php

namespace App\Service\Cart\Condition;

use App\Entity\Inventory;

interface CartConditionInterface
{
    public function apply(Inventory $inventory, int $quantity): void;
}
