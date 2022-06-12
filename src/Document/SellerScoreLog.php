<?php

namespace App\Document;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class SellerScoreLog
{
    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @MongoDB\Id @MongoDB\Index
     */
    protected $id;

    /**
     * @MongoDB\Field(name="sellerId", type="int")
     */
    protected $sellerId;

    /**
     * @MongoDB\Field(name="returnScore", type="int")
     */
    protected $returnScore;

    /**
     * @MongoDB\Field(name="deliveryDelayScore", type="int")
     */
    protected $deliveryDelayScore;

    /**
     * @MongoDB\Field(name="orderCancellationScore", type="int")
     */
    protected $orderCancellationScore;

    /**
     * @MongoDB\Field(name="totalScore", type="int")
     */
    protected $totalScore;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $createdAt;

    public function getSellerId(): int
    {
        return $this->sellerId;
    }

    public function setSellerId(int $sellerId): self
    {
        $this->sellerId = $sellerId;

        return $this;
    }

    public function getReturnScore(): int
    {
        return $this->returnScore;
    }

    public function setReturnScore(int $returnScore): self
    {
        $this->returnScore = $returnScore;

        return $this;
    }

    public function getDeliveryDelayScore(): int
    {
        return $this->deliveryDelayScore;
    }

    public function setDeliveryDelayScore(int $deliveryDelayScore): self
    {
        $this->deliveryDelayScore = $deliveryDelayScore;

        return $this;
    }

    public function getOrderCancellationScore(): int
    {
        return $this->orderCancellationScore;
    }

    public function setOrderCancellationScore(int $orderCancellationScore): self
    {
        $this->orderCancellationScore = $orderCancellationScore;

        return $this;
    }

    public function getTotalScore(): int
    {
        return $this->totalScore;
    }

    public function setTotalScore(int $totalScore): self
    {
        $this->totalScore = $totalScore;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
