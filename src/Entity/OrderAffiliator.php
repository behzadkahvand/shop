<?php

namespace App\Entity;

use App\Repository\OrderAffiliatorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderAffiliatorRepository::class)
 */
class OrderAffiliator
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Order::class, inversedBy="affiliator", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $utmSource;

    /**
     * @ORM\Column(type="string", length=2048)
     */
    private $utmToken;

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

    public function getUtmSource(): ?string
    {
        return $this->utmSource;
    }

    public function setUtmSource(string $utmSource): self
    {
        $this->utmSource = $utmSource;

        return $this;
    }

    public function getUtmToken(): ?string
    {
        return $this->utmToken;
    }

    public function setUtmToken(string $utmToken): self
    {
        $this->utmToken = $utmToken;

        return $this;
    }
}
