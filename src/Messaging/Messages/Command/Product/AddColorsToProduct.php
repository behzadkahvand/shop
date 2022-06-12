<?php

namespace App\Messaging\Messages\Command\Product;

class AddColorsToProduct
{
    public function __construct(private int $productId)
    {
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
}
