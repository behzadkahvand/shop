<?php

namespace App\Service\File\CSV\SellerScore;

use App\Entity\SellerScore;
use App\Service\File\RowAbstract;

final class SellerScoreRow extends RowAbstract
{
    public function __construct(
        private int $sellerId,
        private int $returnScore,
        private int $deliveryDelayScore,
        private int $orderCancellationScore,
        private int $totalScore,
    ) {
    }

    public function getSellerId(): int
    {
        return $this->sellerId;
    }

    public function getReturnScore(): int
    {
        return $this->returnScore;
    }

    public function getDeliveryDelayScore(): int
    {
        return $this->deliveryDelayScore;
    }

    public function getOrderCancellationScore(): int
    {
        return $this->orderCancellationScore;
    }

    public function getTotalScore(): int
    {
        return $this->totalScore;
    }

    public function toSellerScoreEntity(): SellerScore
    {
        return (new SellerScore())
            ->setReturnScore($this->returnScore)
            ->setDeliveryDelayScore($this->deliveryDelayScore)
            ->setOrderCancellationScore($this->orderCancellationScore)
            ->setTotalScore($this->totalScore);
    }
}
