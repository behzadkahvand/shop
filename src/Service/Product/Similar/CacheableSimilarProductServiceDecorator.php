<?php

namespace App\Service\Product\Similar;

use App\Entity\Product;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class CacheableSimilarProductServiceDecorator
 */
final class CacheableSimilarProductServiceDecorator implements SimilarProductServiceInterface
{
    private const CACHE_PREFIX = 'similar_products_category_';

    private SimilarProductServiceInterface $decorated;

    private CacheItemPoolInterface $cache;

    /**
     * CacheableSimilarProductServiceDecorator constructor.
     *
     * @param SimilarProductServiceInterface $decorated
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(SimilarProductServiceInterface $decorated, CacheItemPoolInterface $cache)
    {
        $this->decorated = $decorated;
        $this->cache     = $cache;
    }

    /**
     * @inheritDoc
     */
    public function getSimilarProducts(Product $product): array
    {
        $item = $this->cache->getItem($this->getKey($product));

        if ($item->isHit()) {
            return $item->get();
        }

        $similarProducts = $this->decorated->getSimilarProducts($product);

        $item->set($similarProducts);
        $item->expiresAfter(15 * 60); // 15 minute

        $this->cache->save($item);

        return $similarProducts;
    }

    /**
     * @param Product $product
     *
     * @return string
     */
    private function getKey(Product $product): string
    {
        return self::CACHE_PREFIX . $product->getCategory()->getId();
    }
}
