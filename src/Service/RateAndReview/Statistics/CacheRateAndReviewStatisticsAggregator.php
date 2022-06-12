<?php

namespace App\Service\RateAndReview\Statistics;

use App\Entity\Product;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheRateAndReviewStatisticsAggregator implements RateAndReviewStatisticsServiceInterface
{
    private RateAndReviewStatisticsServiceInterface $rateAndReviewStatistics;

    private CacheInterface $cache;

    public function __construct(RateAndReviewStatisticsServiceInterface $rateAndReviewStatistics, CacheInterface $cache)
    {
        $this->rateAndReviewStatistics = $rateAndReviewStatistics;
        $this->cache = $cache;
    }

    public function getStatistics(Product $product): array
    {
        return $this->cache->get(
            "product_{$product->getId()}_rate_and_review_stats",
            function (ItemInterface $item) use ($product) {
                $result = $this->rateAndReviewStatistics->getStatistics($product);

                $item->expiresAfter(6 * 60 * 60); // 6 Hour

                return $result;
            }
        );
    }
}
