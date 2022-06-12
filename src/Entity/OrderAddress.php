<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\OrderAddressRepository;
use App\Validator\Mobile;
use App\Validator\NationalNumber;
use Doctrine\ORM\Mapping as ORM;
use LongitudeOne\Spatial\PHP\Types\AbstractPoint;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="order_addresses")
 * @ORM\Entity(repositoryClass=OrderAddressRepository::class)
 */
class OrderAddress implements AddressInterface
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"order.show"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Groups({
     *     "order_address.show",
     *     "order.show",
     *     "customer.order.show",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     *     "orderAddress.shipmentPrint",
     *     "orderShipment.show.driver",
     * })
     *
     * @Assert\NotBlank(groups={"order.update"})
     */
    private $fullAddress;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({
     *     "order_address.show",
     *     "order.show",
     *     "customer.order.show",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     *     "orderAddress.shipmentPrint",
     *     "orderShipment.show.driver",
     * })
     *
     * @Assert\NotBlank(groups={"order.update"})
     */
    private $unit;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @Groups({
     *     "order_address.show",
     *     "order.show",
     *     "customer.order.show",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     *     "orderAddress.shipmentPrint",
     *     "orderShipment.show.driver",
     * })
     *
     * @Assert\NotBlank(groups={"order.update"})
     */
    private $floor;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "order_address.show",
     *     "order.show",
     *     "customer.order.show",
     *     "orderShipment.show",
     *     "orderAddress.shipmentPrint",
     *     "orderShipment.show.driver",
     * })
     *
     * @Assert\NotBlank(groups={"order.update"})
     */
    private $number;

    /**
     * @ORM\Column(type="point", length=255, nullable=true)
     *
     * @Groups({"orderAddress.shipmentPrint", "orderShipment.show.driver",})
     */
    private $coordinates;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({
     *     "order_address.show",
     *     "order.show",
     *     "customer.order.show",
     *     "orderShipment.index",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     *     "orderAddress.shipmentPrint",
     *     "orderShipment.show.driver",
     * })
     *
     * @Assert\NotBlank(groups={"order.update"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({
     *     "order_address.show",
     *     "order.show",
     *     "customer.order.show",
     *     "orderShipment.index",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     *     "orderAddress.shipmentPrint",
     *     "orderShipment.show.driver",
     * })
     *
     * @Assert\NotBlank(groups={"order.update"})
     */
    private $family;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"order.show", "orderShipment.show", "orderShipment.show.driver",})
     *
     * @NationalNumber(groups={"order.update"})
     */
    private $nationalCode;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({
     *     "order.show",
     *     "customer.order.show",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     *     "orderAddress.shipmentPrint",
     *     "orderShipment.show.driver",
     * })
     *
     * @Mobile(groups={"order.update"})
     */
    private $phone;

    /**
     * @ORM\Column(type="string")
     * @Groups({
     *     "order.show",
     *     "orderShipment.show",
     *     "orderAddress.shipmentPrint",
     *     "orderShipment.show.driver",
     * })
     *
     * @Assert\Length(min=10, max=10, groups={"order.update"})
     * @Assert\Type(type="numeric", groups={"order.update"})
     * @Assert\NotBlank(groups={"order.update"})
     */
    private $postalCode;

    /**
     * @ORM\ManyToOne(targetEntity=District::class, inversedBy="orderAddresses")
     * @Groups({"order.show", "orderShipment.show"})
     */
    private $district;

    /**
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="orderAddresses")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "order.show",
     *     "customer.order.show",
     *     "orderShipment.index",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     *     "orderAddress.shipmentPrint",
     *     "orderShipment.show.driver",
     * })
     *
     * @Assert\NotBlank(groups={"order.update"})
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="orderAddresses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity=CustomerAddress::class, inversedBy="orderAddresses")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $customerAddress;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $isActive = true;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     *
     * @Groups({
     *     "order_address.show",
     *     "order.show",
     *     "customer.order.show",
     *     "orderShipment.index",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     * })
     */
    private $isForeigner = false;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     *
     * @Groups({
     *     "order_address.show",
     *     "order.show",
     *     "customer.order.show",
     *     "orderShipment.index",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     * })
     */
    private $pervasiveCode;

    public static function fromCustomerAddress(CustomerAddress $customerAddress, ?Order $order = null): self
    {
        $orderAddress = new static();
        $orderAddress->setCity($customerAddress->getCity())
            ->setCoordinates($customerAddress->getCoordinates())
            ->setDistrict($customerAddress->getDistrict())
            ->setFamily($customerAddress->getFamily())
            ->setName($customerAddress->getName())
            ->setNationalCode($customerAddress->getNationalCode())
            ->setFullAddress($customerAddress->getFullAddress())
            ->setNumber($customerAddress->getNumber())
            ->setPhone($customerAddress->getMobile())
            ->setPostalCode($customerAddress->getPostalCode())
            ->setUnit($customerAddress->getUnit())
            ->setPervasiveCode($customerAddress->getPervasiveCode())
            ->setIsForeigner($customerAddress->getIsForeigner())
        ;

        if ($order) {
            $order->addOrderAddress($orderAddress);
        }

        return $orderAddress;
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

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getCoordinates(): ?AbstractPoint
    {
        return $this->coordinates;
    }

    public function setCoordinates(AbstractPoint $point): self
    {
        $this->coordinates = $point;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

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

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): self
    {
        $this->district = $district;

        return $this;
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

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getCustomerAddress(): ?CustomerAddress
    {
        return $this->customerAddress;
    }

    public function setCustomerAddress(?CustomerAddress $customerAddress): self
    {
        $this->customerAddress = $customerAddress;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
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
}
