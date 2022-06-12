<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\SellerScoreRepository;

/**
 * @ORM\Table(name="seller_scores")
 * @ORM\Entity(repositoryClass=SellerScoreRepository::class)
 *
 */
class SellerScore
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
    **/
    private $id;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "customer.product.show",
     *     "product.search.seller.filter",
     *     "seller.auth.profile",
     *     "seller.best_sellers",
     * })
     **/
    private $returnScore;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "customer.product.show",
     *     "product.search.seller.filter",
     *     "seller.auth.profile",
     *     "seller.best_sellers",
     * })
     **/
    private $deliveryDelayScore;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "customer.product.show",
     *     "product.search.seller.filter",
     *     "seller.auth.profile",
     *     "seller.best_sellers",
     * })
     **/
    private $orderCancellationScore;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "customer.product.show",
     *     "product.search.seller.filter",
     *     "seller.auth.profile",
     *     "seller.best_sellers",
     * })
     **/
    private $totalScore;

    public function getId(): int
    {
        return $this->id;
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
}
