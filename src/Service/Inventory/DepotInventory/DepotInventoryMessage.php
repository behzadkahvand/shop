<?php

namespace App\Service\Inventory\DepotInventory;

class DepotInventoryMessage
{
    public function __construct(private array $inventories)
    {
    }

    public function getInventories(): array
    {
        return $this->inventories;
    }
}
