<?php

namespace App\Entity;

use App\Repository\OrderItemLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=OrderItemLogRepository::class)
 */
class OrderItemLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=OrderItem::class, inversedBy="logs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $orderItem;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantityFrom;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantityTo;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class, inversedBy="orderItemLogs")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getQuantityFrom(): ?int
    {
        return $this->quantityFrom;
    }

    public function setQuantityFrom(int $quantityFrom): self
    {
        $this->quantityFrom = $quantityFrom;

        return $this;
    }

    public function getQuantityTo(): ?int
    {
        return $this->quantityTo;
    }

    public function setQuantityTo(int $quantityTo): self
    {
        $this->quantityTo = $quantityTo;

        return $this;
    }

    public function getUser(): ?Admin
    {
        return $this->user;
    }

    public function setUser(?Admin $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
