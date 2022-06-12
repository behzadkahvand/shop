<?php

namespace App\Entity;

use App\Repository\OrderPromotionDiscountRepository;
use App\Service\Promotion\PromotionSubjectInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=OrderPromotionDiscountRepository::class)
 */
class OrderPromotionDiscount extends PromotionDiscount
{
    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="discounts")
     * @ORM\JoinColumn(nullable=true, name="order_id")
     */
    protected $subject;

    /**
     * @ORM\ManyToOne(targetEntity=OrderShipment::class, inversedBy="discounts")
     *
     * @Groups({"promotionDiscount.read"})
     */
    private $orderShipment;

    /**
     * @ORM\ManyToOne(targetEntity=OrderItem::class, inversedBy="discounts")
     *
     * @Groups({"promotionDiscount.read"})
     */
    private $orderItem;

    /**
     * @ORM\Column(type="integer", options={"default"=0})
     *
     * @Groups({
     *     "order.items",
     * })
     */
    private $quantity;

    /**
     * @ORM\Column(type="integer", options={"default"=0})
     */
    private $unitAmount;

    public function getSubjectType()
    {
        return "order";
    }

    public function getOrderShipment(): ?OrderShipment
    {
        return $this->orderShipment;
    }

    public function setOrderShipment(?OrderShipment $orderShipment): self
    {
        $this->orderShipment = $orderShipment;

        return $this;
    }

    public function getOrderItem(): ?OrderItem
    {
        return $this->orderItem;
    }

    public function setOrderItem(?OrderItem $orderItem): self
    {
        $this->orderItem = $orderItem;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitAmount(): ?int
    {
        return $this->unitAmount;
    }

    public function setUnitAmount(int $unitAmount): self
    {
        $this->unitAmount = $unitAmount;

        return $this;
    }
}
