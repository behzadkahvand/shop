<?php

namespace App\Messaging\Messages\Command\Product;

class ProcessInventoryUpdateSheet
{
    public function __construct(private int $inventoryUpdateSheetId)
    {
    }

    public function getInventoryUpdateSheetId(): int
    {
        return $this->inventoryUpdateSheetId;
    }
}
