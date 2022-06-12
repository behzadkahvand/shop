<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\DistrictRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="districts")
 * @ORM\Entity(repositoryClass=DistrictRepository::class)
 */
class District
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"default", "customer.read", "order.show"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="districts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"default", "customer.read", "order.show", "orderShipment.show"})
     */
    private $name;

    /**
     * @ORM\Column(type="multipolygon", length=255, nullable=true)
     */
    private $coordinates;

    /**
     * @ORM\OneToMany(targetEntity=OrderAddress::class, mappedBy="district", orphanRemoval=true)
     */
    private $orderAddresses;

    public function __construct()
    {
        $this->orderAddresses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCoordinates(): ?string
    {
        return $this->coordinates;
    }

    public function setCoordinates(string $coordinates): self
    {
        $this->coordinates = $coordinates;

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
     * @return Collection|OrderAddress[]
     */
    public function getOrderAddresses(): Collection
    {
        return $this->orderAddresses;
    }

    public function addOrderAddress(OrderAddress $orderAddress): self
    {
        if (!$this->orderAddresses->contains($orderAddress)) {
            $this->orderAddresses[] = $orderAddress;
            $orderAddress->setDistrict($this);
        }

        return $this;
    }

    public function removeOrderAddress(OrderAddress $orderAddress): self
    {
        if ($this->orderAddresses->contains($orderAddress)) {
            $this->orderAddresses->removeElement($orderAddress);
            // set the owning side to null (unless already changed)
            if ($orderAddress->getDistrict() === $this) {
                $orderAddress->setDistrict(null);
            }
        }

        return $this;
    }
}
