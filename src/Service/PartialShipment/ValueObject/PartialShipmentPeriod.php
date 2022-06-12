<?php

namespace App\Service\PartialShipment\ValueObject;

use App\Entity\ShippingPeriod;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class PartialShipmentPeriod
 */
final class PartialShipmentPeriod implements \JsonSerializable
{
    /**
     * @var ShippingPeriod
     */
    private ShippingPeriod $shippingPeriod;

    /**
     * @var int
     */
    private int $id;

    /**
     * @var \DateTimeInterface
     * @Groups({"cart.shipments"})
     */
    private \DateTimeInterface $start;

    /**
     * @var \DateTimeInterface
     * @Groups({"cart.shipments"})
     */
    private \DateTimeInterface $end;

    /**
     * @var bool
     * @Groups({"cart.shipments"})
     */
    private bool $isSelectable;

    /**
     * PartialShipmentPeriod constructor.
     *
     * @param ShippingPeriod $shippingPeriod
     * @param bool $isSelectable
     *
     * @throws \Exception
     */
    public function __construct(ShippingPeriod $shippingPeriod, bool $isSelectable = true)
    {
        $this->shippingPeriod = $shippingPeriod;
        $this->id           = $shippingPeriod->getId();
        $this->start        = new \DateTimeImmutable($shippingPeriod->getStart()->format('H:i'));
        $this->end          = new \DateTimeImmutable($shippingPeriod->getEnd()->format('H:i'));
        $this->isSelectable = $isSelectable;
    }

    /**
     * @param ShippingPeriod $shippingPeriod
     *
     * @return PartialShipmentPeriod
     */
    public static function fromShippingPeriod(ShippingPeriod $shippingPeriod): self
    {
        return new self($shippingPeriod);
    }

    /**
     * @return ShippingPeriod
     */
    public function getShippingPeriod(): ShippingPeriod
    {
        return $this->shippingPeriod;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStart(): \DateTimeInterface
    {
        return $this->start;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }

    /**
     * @return bool
     */
    public function isSelectable(): bool
    {
        return $this->isSelectable;
    }

    /**
     * @param bool $isSelectable
     *
     * @return PartialShipmentPeriod
     */
    public function setIsSelectable(bool $isSelectable): self
    {
        $new = clone $this;

        $new->isSelectable = $isSelectable;

        return $new;
    }

    /**
     * @param $datetime
     *
     * @return bool
     */
    public function startTimeEquals($datetime): bool
    {
        $datetime = to_date_time($datetime);

        return $this->start->format('H:i') === $datetime->format('H:i');
    }

    /**
     * @param \DateTimeInterface $deliveryDatetime
     *
     * @return bool
     */
    public function isOpenForDelivery(\DateTimeInterface $deliveryDatetime): bool
    {
        return $deliveryDatetime->format('H:i') < $this->start->format('H:i');
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'start'      => $this->start->format('H:i'),
            'end'        => $this->end->format('H:i'),
            'selectable' => $this->isSelectable,
        ];
    }
}
