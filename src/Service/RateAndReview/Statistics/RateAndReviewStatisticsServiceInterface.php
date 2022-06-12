<?php

namespace App\Service\RateAndReview\Statistics;

use App\Entity\Product;

interface RateAndReviewStatisticsServiceInterface
{
    public function getStatistics(Product $product): array;
}
