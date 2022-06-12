<?php

namespace App\Service\Product\Similar;

use App\Entity\Product;

/**
 * Interface SimilarProductServiceInterface
 */
interface SimilarProductServiceInterface
{
    /**
     * @param Product $product
     *
     * @return array
     */
    public function getSimilarProducts(Product $product): array;
}
