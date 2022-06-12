<?php

namespace App\Service\Product\Update\PropertyUpdaters;

use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Service\Product\Update\PropertyUpdater;

class BrandUpdater implements PropertyUpdater
{
    public function __construct(protected BrandRepository $brandRepository)
    {
    }

    public function update(Product $product, array $dkp): void
    {
        $brandName = $dkp['product']['brand']['title_fa'];

        $brand = $this->brandRepository->findOneBy(['title' => $brandName]);
        if (null === $brand) {
            $brand = $this->brandRepository->findOneBy(['title' => 'متفرقه']);
        }

        $product->setBrand($brand);
    }
}
