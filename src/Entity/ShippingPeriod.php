<?php

namespace App\Entity;

use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use App\Repository\ShippingPeriodRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="shipping_periods")
 * @ORM\Entity(repositoryClass=ShippingPeriodRepository::class)
 */
class ShippingPeriod
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({"shipping.period.index", "shipping.period.store", "shipping.period.update",
     *          "shipping-methods.grid"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="time")
     *
     * @Assert\NotBlank(groups={"shipping.period.store"})
     * @Assert\NotNull(groups={"shipping.period.store"})
     *
     * @Groups({
     *     "orderShipment.shipmentPrint",
     * })
     */
    private $start;

    /**
     * @ORM\Column(type="time")
     *
     * @Assert\NotBlank(groups={"shipping.period.store"})
     * @Assert\NotNull(groups={"shipping.period.store"})
     *
     * @Groups({
     *     "orderShipment.shipmentPrint",
     * })
     */
    private $end;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\NotNull(groups={"shipping.period.store", "shipping.period.update"})
     *
     * @Groups({"shipping.period.index", "shipping.period.store", "shipping.period.update",
     *          "shipping-methods.grid"
     * })
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity=OrderShipment::class, mappedBy="period")
     */
    private $shipments;

    public function __construct()
    {
        $this->shipments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setStart(?DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setEnd(?DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @Groups({
     *     "shipping.period.index",
     *     "shipping.period.store",
     *     "shipping.period.update",
     *     "shipping-methods.grid",
     *     "orderShipment.show",
     *     "orderShipment.index",
     *     "customer.order.show",
     * })
     *
     * @SerializedName("start")
     */
    public function getFormattedStart(): string
    {
        return $this->getStart()->format('H:i');
    }

    /**
     * @Groups({
     *     "shipping.period.index",
     *     "shipping.period.store",
     *     "shipping.period.update",
     *     "shipping-methods.grid",
     *     "orderShipment.index",
     *     "orderShipment.show",
     *     "customer.order.show",
     * })
     *
     * @SerializedName("end")
     */
    public function getFormattedEnd(): string
    {
        return $this->getEnd()->format('H:i');
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
        if (! $this->shipments->contains($shipment)) {
            $this->shipments[] = $shipment;
            $shipment->setPeriod($this);
        }

        return $this;
    }

    public function removeShipment(OrderShipment $shipment): self
    {
        if ($this->shipments->contains($shipment)) {
            $this->shipments->removeElement($shipment);
            // set the owning side to null (unless already changed)
            if ($shipment->getPeriod() === $this) {
                $shipment->setPeriod(null);
            }
        }

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
}
