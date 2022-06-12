<?php

namespace App\Entity;

use App\Entity\Common\CodeAwareInterface;
use App\Entity\Common\Timestampable;
use App\Repository\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="zones")
 * @ORM\Entity(repositoryClass=ZoneRepository::class)
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="dtype", type="string")
 * @ORM\DiscriminatorMap(
 *     {"city" = "CityZone", "district" = "DistrictZone", "generic" ="GenericZone", "province" = "ProvinceZone"}
 * )
 * @UniqueEntity(fields={"name"}, groups={"zone.create"})
 * @UniqueEntity(fields={"code"}, groups={"zone.create"})
 */
abstract class Zone implements CodeAwareInterface
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"default", "shipping-methods.grid"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"zone.create"})
     * @Gedmo\Slug(fields={"name"})
     * @Groups({"default", "shipping-methods.grid", "shipping-method-prices.index"})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"zone.create"})
     * @Groups({"default", "shipping-methods.grid", "shipping-method-prices.index"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=ShippingMethodPrice::class, mappedBy="zone", orphanRemoval=true)
     */
    private $shippingMethodPrices;

    public function __construct()
    {
        $this->shippingMethodPrices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|ShippingMethodPrice[]
     */
    public function getShippingMethodPrices(): Collection
    {
        return $this->shippingMethodPrices;
    }

    public function addShippingMethodPrice(ShippingMethodPrice $shippingMethodPrice): self
    {
        if (!$this->shippingMethodPrices->contains($shippingMethodPrice)) {
            $this->shippingMethodPrices[] = $shippingMethodPrice;
            $shippingMethodPrice->setZone($this);
        }

        return $this;
    }

    public function removeShippingMethodPrice(ShippingMethodPrice $shippingMethodPrice): self
    {
        if ($this->shippingMethodPrices->contains($shippingMethodPrice)) {
            $this->shippingMethodPrices->removeElement($shippingMethodPrice);
            // set the owning side to null (unless already changed)
            if ($shippingMethodPrice->getZone() === $this) {
                $shippingMethodPrice->setZone(null);
            }
        }

        return $this;
    }
}
