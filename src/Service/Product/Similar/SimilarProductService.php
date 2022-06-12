<?php

namespace App\Service\Product\Similar;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SimilarProductService implements SimilarProductServiceInterface
{
    protected NormalizerInterface $normalizer;

    protected ProductRepository $productRepository;

    public function __construct(
        NormalizerInterface $normalizer,
        ProductRepository $productRepository
    ) {
        $this->normalizer        = $normalizer;
        $this->productRepository = $productRepository;
    }

    public function getSimilarProducts(Product $product): array
    {
        $similarProducts = $this->productRepository->getSimilarProducts($product);

        return $this->normalizer->normalize($similarProducts, null, [
            'groups' => ['product.search'],
        ]);
    }
}
