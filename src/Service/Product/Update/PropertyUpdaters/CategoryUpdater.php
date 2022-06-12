<?php

namespace App\Service\Product\Update\PropertyUpdaters;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Service\Product\Update\PropertyUpdater;

class CategoryUpdater implements PropertyUpdater
{
    public function __construct(protected CategoryRepository $categoryRepository)
    {
    }

    public function update(Product $product, array $dkp): void
    {
        $categoryName = $dkp['product']['category']['title_fa'];

        $category = $categoryName ? $this->categoryRepository->findLeafCategoryByTitle($categoryName) : null;
        if (null === $category) {
            $category = $this->categoryRepository->findOneRandomLeafCategory();
        }

        $product->setCategory($category);
    }
}
