<?php

namespace App\Entity;

use App\Dictionary\CityDictionary;
use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="cities")
 * @ORM\Entity(repositoryClass=CityRepository::class)
 */
class City
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({
     *     "default",
     *     "customer.read",
     *     "order.show",
     *     "city.details",
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "order.legal.account.store",
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Province::class, inversedBy="cities")
     *
     * @Groups({
     *     "orderShipment.index",
     *     "order.show",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     *     "city.details",
     *     "orderShipment.show.driver",
     * })
     */
    private $province;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({
     *     "default",
     *     "customer.read",
     *     "order.show",
     *     "customer.order.show",
     *     "orderShipment.index",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     *     "city.details",
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "order.legal.account.store",
     *     "orderShipment.show.driver",
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="multipolygon", length=255, nullable=true)
     *
     * @Groups({"city.details"})
     */
    private $coordinates;

    /**
     * @ORM\OneToMany(targetEntity=OrderAddress::class, mappedBy="city", orphanRemoval=true)
     */
    private $orderAddresses;

    /**
     * @ORM\OneToMany(targetEntity=District::class, mappedBy="city", orphanRemoval=true)
     */
    private $districts;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    public function __construct()
    {
        $this->orderAddresses = new ArrayCollection();
        $this->districts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProvince(): ?Province
    {
        return $this->province;
    }

    public function setProvince(?Province $province): self
    {
        $this->province = $province;

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

    public function getCoordinates(): ?string
    {
        return $this->coordinates;
    }

    public function setCoordinates(string $coordinates): self
    {
        $this->coordinates = $coordinates;

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
            $orderAddress->setCity($this);
        }

        return $this;
    }

    public function removeOrderAddress(OrderAddress $orderAddress): self
    {
        if ($this->orderAddresses->contains($orderAddress)) {
            $this->orderAddresses->removeElement($orderAddress);
            // set the owning side to null (unless already changed)
            if ($orderAddress->getCity() === $this) {
                $orderAddress->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|District[]
     */
    public function getDistricts(): Collection
    {
        return $this->districts;
    }

    /**
     * @return bool
     */
    public function isExpress(): bool
    {
        return in_array($this->name, CityDictionary::EXPRESS_CITIES, true);
    }
}
