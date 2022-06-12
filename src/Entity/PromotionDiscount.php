<?php

namespace App\Entity;

use App\Repository\PromotionDiscountRepository;
use App\Service\Promotion\PromotionSubjectInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PromotionDiscountRepository::class)
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="subject_type", type="string")
 * @ORM\DiscriminatorMap({
 *     "order" = OrderPromotionDiscount::class,
 *     "cart" = CartPromotionDiscount::class
 * })
 *
 * @DiscriminatorMap(typeProperty="subjectType", mapping={
 *    "order"="App\Entity\OrderPromotionDiscount",
 *    "cart"="App\Entity\CartPromotionDiscount"
 * })
 */
abstract class PromotionDiscount
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"promotionDiscount.read"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity=PromotionAction::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"promotionDiscount.details"})
     */
    protected $action;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "promotionDiscount.read",
     *     "order.items",
     * })
     */
    protected $amount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): ?PromotionAction
    {
        return $this->action;
    }

    public function setAction(?PromotionAction $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSubject(): ?PromotionSubjectInterface
    {
        return $this->subject;
    }

    public function setSubject(?PromotionSubjectInterface $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @Groups({"promotionDiscount.read"})
     */
    public function isViaCoupon()
    {
        return $this->getAction()->getPromotion()->getCouponBased();
    }

    abstract public function getSubjectType();
}
