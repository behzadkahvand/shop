<?php

namespace App\Service\RateAndReview\Statistics;

use App\Entity\Product;
use App\Repository\RateAndReviewRepository;

class RatesPerValueStatistic implements RateAndReviewStatisticsInterface
{
    private RateAndReviewRepository $repository;

    public function __construct(RateAndReviewRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getStatistic(Product $product): array
    {
        return [
            'rates_per_value' => $this->repository->findRatesPerValueForProduct($product),
        ];
    }
}
