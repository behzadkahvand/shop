<?php

namespace App\Messaging\Messages\Command\Product;

final class LogInventoryPriceChange
{
    public function __construct(
        private int $inventoryId,
        private ?int $oldPrice,
        private ?int $oldFinalPrice,
        private ?int $userId
    ) {
    }

    public function getInventoryId(): int
    {
        return $this->inventoryId;
    }

    public function getOldPrice(): ?int
    {
        return $this->oldPrice;
    }

    public function getOldFinalPrice(): ?int
    {
        return $this->oldFinalPrice;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }
}
