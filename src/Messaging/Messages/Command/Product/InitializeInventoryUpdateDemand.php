<?php

namespace App\Messaging\Messages\Command\Product;

class InitializeInventoryUpdateDemand
{
    public function __construct(private int $inventoryUpdateDemandId)
    {
    }

    public function getInventoryUpdateDemandId(): int
    {
        return $this->inventoryUpdateDemandId;
    }
}
