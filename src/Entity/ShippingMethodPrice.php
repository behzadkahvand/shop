<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\ShippingMethodPriceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ShippingMethodPriceRepository::class)
 */
class ShippingMethodPrice
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"shipping-methods.grid"})
     */
    private $id;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @Assert\NotBlank(groups={"admin.shipping.method.store"})
     * @Assert\PositiveOrZero(groups={"admin.shipping.method.store","admin.shipping.method.update"})
     * @Groups({"shipping-methods.grid", "shipping-method-prices.index"})
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity=Zone::class, inversedBy="shippingMethodPrices")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(groups={"admin.shipping.method.store","admin.shipping.method.update"})
     * @Groups({"shipping-methods.grid", "shipping-method-prices.index"})
     */
    private $zone;

    /**
     * @ORM\ManyToOne(targetEntity=ShippingMethod::class, inversedBy="shippingMethodPrices")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(groups={"admin.shipping.method.store","admin.shipping.method.update"})
     * @Groups({"shipping-method-prices.index"})
     */
    private $shippingMethod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function getShippingMethod(): ?ShippingMethod
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(?ShippingMethod $shippingMethod): self
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }
}
