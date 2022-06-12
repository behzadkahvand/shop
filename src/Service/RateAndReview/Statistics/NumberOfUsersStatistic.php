<?php

namespace App\Service\RateAndReview\Statistics;

use App\Entity\Product;
use App\Repository\RateAndReviewRepository;

class NumberOfUsersStatistic implements RateAndReviewStatisticsInterface
{
    private RateAndReviewRepository $repository;

    public function __construct(RateAndReviewRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getStatistic(Product $product): array
    {
        return [
            'number_of_users' => $this->repository->findNumbersOfUsersWhoRatedForProduct($product),
        ];
    }
}
