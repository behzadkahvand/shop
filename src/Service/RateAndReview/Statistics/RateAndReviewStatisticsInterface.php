<?php

namespace App\Service\RateAndReview\Statistics;

use App\Entity\Product;

interface RateAndReviewStatisticsInterface
{
    public function getStatistic(Product $product): array;
}
