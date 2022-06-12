<?php

namespace App\Service\Product\Update;

use App\Entity\Product;

interface PropertyUpdater
{
    public function update(Product $product, array $dkp): void;
}
