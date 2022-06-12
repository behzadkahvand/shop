<?php

namespace App\Service\Product\Colors;

use App\Entity\Inventory;
use App\Entity\Product;

class ProductColorsListener
{
    public function __construct(private AddColorsService $addColorsService)
    {
    }

    public function onInventoryPostInsertOrUpdate(Inventory $inventory): void
    {
        $product = $inventory->getProduct();

        $this->addColorsService->add($product->getId());
    }
}
