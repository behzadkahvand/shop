<?php

namespace App\Entity;

use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use App\Repository\ShippingMethodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="shipping_methods")
 * @ORM\Entity(repositoryClass=ShippingMethodRepository::class)
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="This method is already exists.",
 *     groups={"admin.shipping.method.store","admin.shipping.method.update"}
 * )
 */
class ShippingMethod
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({
     *     "default",
     *     "shipping-methods.grid",
     *     "shipping-method-prices.index",
     *     "orderShipment.show",
     *     "orderShipment.shipmentPrint",
     *     "orderShipment.index",
     *     "order.index",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={"admin.shipping.method.store","admin.shipping.method.update"})
     *
     * @Groups({
     *     "default",
     *     "shipping-methods.grid",
     *     "customer.order.show",
     *     "shipping-method-prices.index",
     *     "orderShipment.show",
     *     "orderShipment.shipmentPrint",
     *     "orderShipment.index",
     *     "order.index",
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank(groups={"admin.shipping.method.store","admin.shipping.method.update"})
     * @Groups({
     *     "default",
     *     "shipping-methods.grid",
     *     "shipping-method-prices.index",
     *     "orderShipment.show",
     *     "orderShipment.index",
     *     "order.index",
     * })
     */
    private $code;

    /**
     * @var string[]
     *
     * @ORM\Column(type="json")
     * @Groups({"default", "shipping-methods.grid"})
     */
    private $configuration = [];

    /**
     * @ORM\ManyToMany(targetEntity=ShippingCategory::class, mappedBy="methods", cascade={"persist"})
     * @Groups({"shipping-methods.grid"})
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity=OrderShipment::class, mappedBy="method")
     */
    private $shipments;

    /**
     * @ORM\OneToMany(targetEntity=ShippingMethodPrice::class, mappedBy="shippingMethod", orphanRemoval=true,cascade={"persist","remove"})
     * @Assert\Valid(groups={"admin.shipping.method.store","admin.shipping.method.update"})
     * @Groups({"shipping-methods.grid"})
     */
    private $shippingMethodPrices;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->shipments = new ArrayCollection();
        $this->shippingMethodPrices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
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

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function setConfiguration($configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @return Collection|ShippingCategory[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(ShippingCategory $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->addMethod($this);
        }

        return $this;
    }

    public function removeCategory(ShippingCategory $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            $category->removeMethod($this);
        }

        return $this;
    }

    /**
     * @return Collection|OrderShipment[]
     */
    public function getShipments(): Collection
    {
        return $this->shipments;
    }

    public function addShipment(OrderShipment $shipment): self
    {
        if (!$this->shipments->contains($shipment)) {
            $this->shipments[] = $shipment;
            $shipment->setMethod($this);
        }

        return $this;
    }

    public function removeShipment(OrderShipment $shipment): self
    {
        if ($this->shipments->contains($shipment)) {
            $this->shipments->removeElement($shipment);
            // set the owning side to null (unless already changed)
            if ($shipment->getMethod() === $this) {
                $shipment->setMethod(null);
            }
        }

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
            $shippingMethodPrice->setShippingMethod($this);
        }

        return $this;
    }

    public function removeShippingMethodPrice(ShippingMethodPrice $shippingMethodPrice): self
    {
        if ($this->shippingMethodPrices->contains($shippingMethodPrice)) {
            $this->shippingMethodPrices->removeElement($shippingMethodPrice);
            // set the owning side to null (unless already changed)
            if ($shippingMethodPrice->getShippingMethod() === $this) {
                $shippingMethodPrice->setShippingMethod(null);
            }
        }

        return $this;
    }
}
