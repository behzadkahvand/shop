<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\CustomerAddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use LongitudeOne\Spatial\PHP\Types\AbstractPoint;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="customer_addresses")
 * @ORM\Entity(repositoryClass=CustomerAddressRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class CustomerAddress implements AddressInterface
{
    use Timestampable;

    use SoftDeleteableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"customer.read", "default"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $fullAddress;

    /**
     * @ORM\Column(type="string")
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $postalCode;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $isDefault = false;

    /**
     * @ORM\Column(type="point", length=255, nullable=true)
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $coordinates;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $number;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $unit;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $floor;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $family;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $nationalCode;

    /**
     * @ORM\Column(type="string", length=15)
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $mobile;

    /**
     * @ORM\ManyToOne(targetEntity=Province::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $province;

    /**
     * @ORM\ManyToOne(targetEntity=City::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity=District::class)
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $district;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="addresses", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\OneToMany(targetEntity=OrderAddress::class, mappedBy="customerAddress")
     */
    private $orderAddresses;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     *
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $isForeigner = false;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     *
     * @Groups({"customer.read", "customer.update", "default"})
     */
    private $pervasiveCode;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullAddress(): ?string
    {
        return $this->fullAddress;
    }

    public function setFullAddress(string $fullAddress): self
    {
        $this->fullAddress = $fullAddress;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getCoordinates(): ?AbstractPoint
    {
        return $this->coordinates;
    }

    public function setCoordinates(AbstractPoint $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getUnit(): ?int
    {
        return $this->unit;
    }

    public function setUnit(?int $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getFloor(): ?string
    {
        return $this->floor;
    }

    public function setFloor(?string $floor): self
    {
        $this->floor = $floor;

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

    public function getFamily(): ?string
    {
        return $this->family;
    }

    public function setFamily(string $family): self
    {
        $this->family = $family;

        return $this;
    }

    public function getNationalCode(): string
    {
        return $this->nationalCode;
    }

    public function setNationalCode(string $nationalCode): self
    {
        $this->nationalCode = $nationalCode;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getProvince(): Province
    {
        return $this->province;
    }

    /**
     * @param mixed $province
     * @return CustomerAddress
     */
    public function setProvince(Province $province): self
    {
        $this->province = $province;
        return $this;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function setCity(City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): self
    {
        $this->district = $district;

        return $this;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection|OrderAddress[]
     */
    public function getOrderAddresses()
    {
        return $this->orderAddresses;
    }

    /**
     * @param mixed $orderAddresses
     */
    public function setOrderAddresses($orderAddresses): void
    {
        $this->orderAddresses = $orderAddresses;
    }

    public function getIsForeigner(): ?bool
    {
        return $this->isForeigner;
    }

    public function setIsForeigner(bool $isForeigner): self
    {
        $this->isForeigner = $isForeigner;

        return $this;
    }

    public function getPervasiveCode(): ?string
    {
        return $this->pervasiveCode;
    }

    public function setPervasiveCode(?string $pervasiveCode): self
    {
        $this->pervasiveCode = $pervasiveCode;

        return $this;
    }

    public function isCityExpress(): bool
    {
        return $this->getCity()->isExpress();
    }
}
