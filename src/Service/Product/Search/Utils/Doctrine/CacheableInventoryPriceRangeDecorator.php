<?php

namespace App\Service\Product\Search\Utils\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Psr\Cache\CacheItemPoolInterface;

final class CacheableInventoryPriceRangeDecorator implements InventoryPriceRangeInterface
{
    public const CACHE_PREFIX = 'inventory_price_range_';

    public function __construct(
        private InventoryPriceRangeInterface $decorated,
        private CacheItemPoolInterface $cache,
        private int $cacheExpireTime
    ) {
    }

    public function getPriceRange(QueryBuilder $queryBuilder, array $filters): array
    {
        if ($this->cacheExpireTime <= 0 || count($filters) > 1) {
            return $this->getDecoratedPriceRange($queryBuilder, $filters);
        }

        $filterKey = collect($filters)->keys()->first() ?? 'all';

        if (!empty($filters) && !$this->hasCacheableFilter($filterKey)) {
            return $this->getDecoratedPriceRange($queryBuilder, $filters);
        }

        $filterValue = $filters[$filterKey] ?? '';

        $item = $this->cache->getItem($this->getCacheKey($filterKey, $filterValue));

        if ($item->isHit()) {
            return $item->get();
        }

        $range = $this->getDecoratedPriceRange($queryBuilder, $filters);

        $item->set($range)
             ->expiresAfter($this->cacheExpireTime);

        $this->cache->save($item);

        return $range;
    }

    private function getCacheKey(string $filterKey, array|string $filterValue): string
    {
        $filterValues = is_array($filterValue) ? array_pop($filterValue) : $filterValue;
        $separator = empty($filterValues) ? '' : '_';

        return self::CACHE_PREFIX . $this->cacheableFilters()[$filterKey] . $separator . $filterValues;
    }

    private function hasCacheableFilter(string $filterKey): bool
    {
        return in_array($filterKey, array_keys($this->cacheableFilters()));
    }

    private function cacheableFilters(): array
    {
        return [
            'all'                                           => 'all',
            'category.id'                                   => 'category',
            'productVariants.inventories.seller.identifier' => 'seller',
            'brand.code'                                    => 'brand'
        ];
    }

    private function getDecoratedPriceRange(QueryBuilder $queryBuilder, array $filters): array
    {
        return $this->decorated->getPriceRange($queryBuilder, $filters);
    }
}
