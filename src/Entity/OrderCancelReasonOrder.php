<?php

namespace App\Entity;

use App\Repository\OrderCancelReasonOrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="order_cancel_reason_orders")
 * @ORM\Entity(repositoryClass=OrderCancelReasonOrderRepository::class)
 */
class OrderCancelReasonOrder
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Order::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity=OrderCancelReason::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $cancelReason;

    /**
     * OrderCancelReasonOrder constructor.
     *
     * @param $order
     * @param $cancelReason
     */
    public function __construct(Order $order, OrderCancelReason $cancelReason)
    {
        $this->order        = $order;
        $this->cancelReason = $cancelReason;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getCancelReason(): ?OrderCancelReason
    {
        return $this->cancelReason;
    }

    public function setCancelReason(OrderCancelReason $cancelReason): self
    {
        $this->cancelReason = $cancelReason;

        return $this;
    }
}
