<?php

namespace App\Service\Product\BuyBox;

use App\Entity\Inventory;
use App\Entity\Product;

final class ProductBuyBoxListener
{
    protected AddBuyBoxService $addBuyBoxService;

    public function __construct(AddBuyBoxService $addBuyBoxService)
    {
        $this->addBuyBoxService = $addBuyBoxService;
    }

    public function onInventoryPostInsertOrUpdate(Inventory $inventory): void
    {
        /**
         * @var Product $product
         */
        $product = $inventory->getVariant()->getProduct();

        $this->addBuyBoxService->addOne($product->getId());
    }
}
