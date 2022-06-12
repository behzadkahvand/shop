<?php

namespace App\Messaging\Messages\Command\Product;

final class AddBuyBoxToProduct
{
    public function __construct(protected int $productId)
    {
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
}
