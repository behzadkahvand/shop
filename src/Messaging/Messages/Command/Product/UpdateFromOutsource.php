<?php

namespace App\Messaging\Messages\Command\Product;

class UpdateFromOutsource
{
    public function __construct(protected int $productId)
    {
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
}
