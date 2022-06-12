<?php

namespace App\Service\RateAndReview\Statistics;

use App\Entity\Product;

class RateAndReviewStatisticsAggregator implements RateAndReviewStatisticsServiceInterface
{
    protected iterable $statistics;

    public function __construct(iterable $statistics)
    {
        $this->statistics = $statistics;
    }

    public function getStatistics(Product $product): array
    {
        $result = [];

        /** @var RateAndReviewStatisticsInterface $statistic */
        foreach ($this->statistics as $statistic) {
            foreach ($statistic->getStatistic($product) as $key => $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
