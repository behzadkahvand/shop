<?php

namespace App\Service\Product\Campaign;

class BlackFridayRequest
{
    public function __construct(protected int $finalPrice, protected int $stock)
    {
    }

    public function getFinalPrice(): int
    {
        return $this->finalPrice;
    }

    public function getStock(): int
    {
        return $this->stock;
    }
}
