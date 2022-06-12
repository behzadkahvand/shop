<?php

namespace App\Service\Product\Similar;

use App\Entity\Product;

/**
 * Class ExcludeCurrentProductSimilarProductServiceDecorator
 */
final class ExcludeCurrentProductSimilarProductServiceDecorator implements SimilarProductServiceInterface
{
    private SimilarProductServiceInterface $decorated;

    /**
     * ExcludeCurrentProductSimilarProductServiceDecorator constructor.
     *
     * @param SimilarProductServiceInterface $decorated
     */
    public function __construct(SimilarProductServiceInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @inheritDoc
     */
    public function getSimilarProducts(Product $product): array
    {
        $productId       = $product->getId();
        $similarProducts = $this->decorated->getSimilarProducts($product);

        foreach ($similarProducts as $index => $similarProduct) {
            if ($productId === $similarProduct['id']) {
                unset($similarProducts[$index]);

                $similarProducts = array_values($similarProducts);

                break;
            }
        }

        return $similarProducts;
    }
}
