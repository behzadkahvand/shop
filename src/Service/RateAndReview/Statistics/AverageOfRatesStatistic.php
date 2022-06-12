<?php

namespace App\Service\RateAndReview\Statistics;

use App\Entity\Product;
use App\Repository\RateAndReviewRepository;

class AverageOfRatesStatistic implements RateAndReviewStatisticsInterface
{
    private RateAndReviewRepository $repository;

    public function __construct(RateAndReviewRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getStatistic(Product $product): array
    {
        return [
            'average_of_rate' => $this->repository->findAverageOfRatesForProduct($product),
        ];
    }
}
