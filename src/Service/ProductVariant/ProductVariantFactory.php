<?php

namespace App\Service\ProductVariant;

use App\Entity\Inventory;
use App\Entity\ProductVariant;

class ProductVariantFactory
{
    public function getProductVariant(): ProductVariant
    {
        return new ProductVariant();
    }

    public function getInventory(): Inventory
    {
        return new Inventory();
    }
}
