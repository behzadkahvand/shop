<?php

namespace App\Service\Product\Update\PropertyUpdaters;

use App\Entity\Product;
use App\Service\Product\Update\PropertyUpdater;

class TitleUpdater implements PropertyUpdater
{
    public function update(Product $product, array $dkp): void
    {
        $product->setTitle($dkp['product']['title_fa']);
    }
}
