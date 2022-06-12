<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\AbandonedNotificationLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AbandonedNotificationLogRepository::class)
 */
class AbandonedNotificationLog
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Cart::class, inversedBy="abandonedNotificationLog")
     * @ORM\JoinColumn(nullable=false, unique=true, onDelete="CASCADE")
     */
    private $cart;

    /**
     * @ORM\Column(type="integer", options={"default"=0})
     */
    private $total_sent = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    public function getTotalSent(): int
    {
        return $this->total_sent;
    }

    public function setTotalSent(int $total_sent): self
    {
        $this->total_sent = $total_sent;

        return $this;
    }
}
