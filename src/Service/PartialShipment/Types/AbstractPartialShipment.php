<?php

namespace App\Service\PartialShipment\Types;

use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Entity\Zone;
use App\Service\PartialShipment\Exceptions\PartialShipmentCanNotBeFreezedException;
use App\Service\PartialShipment\ValueObject\BaseFreezedPartialShipment;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\PartialShipment\ValueObject\PartialShipmentPrice;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Class PartialShipment
 */
abstract class AbstractPartialShipment
{
    private static int $index = 1;

    private int $id;

    protected ShippingCategory $shippingCategory;

    protected array $shipmentItems;

    private ShippingMethod $shippingMethod;

    private PartialShipmentPrice $price;

    protected DateTimeImmutable $baseDeliveryDate;

    protected array $calculatedDeliveryDates = [];

    protected ?string $description = null;

    private Zone $zone;

    /**
     * PartialShipment constructor.
     *
     * @param ShippingCategory $shippingCategory
     * @param Zone $zone
     * @param array|PartialShipmentItem[] $shipmentItems
     */
    public function __construct(ShippingCategory $shippingCategory, Zone $zone, array $shipmentItems)
    {
        $this->shippingCategory = $shippingCategory;
        $this->zone             = $zone;
        $this->shipmentItems    = $shipmentItems;
    }

    public static function resetId(): void
    {
        self::$index = 1;
    }

    public function getId(): int
    {
        if (!isset($this->id)) {
            $this->id = self::$index++;
        }

        return $this->id;
    }

    public function getShippingCategory(): ShippingCategory
    {
        return $this->shippingCategory;
    }

    public function getShipmentItems(): array
    {
        return $this->shipmentItems;
    }

    public function getBaseDeliveryDate(): DateTimeImmutable
    {
        return $this->baseDeliveryDate;
    }

    public function setBaseDeliveryDate(DateTimeImmutable $baseDeliveryDate): void
    {
        $this->baseDeliveryDate = $baseDeliveryDate;
    }

    public function getShippingMethod(): ShippingMethod
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(ShippingMethod $shippingMethod): void
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getZone(): Zone
    {
        return $this->zone;
    }

    public function getPrice(): PartialShipmentPrice
    {
        return $this->price;
    }

    public function setPrice(PartialShipmentPrice $price): void
    {
        $this->price = $price;
    }

    public function getCalculatedDeliveryDates(): array
    {
        return $this->calculatedDeliveryDates;
    }

    public function setCalculatedDeliveryDates(array $calculatedDeliveryDates): void
    {
        $this->calculatedDeliveryDates = $calculatedDeliveryDates;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function freeze(DateTimeInterface $selectedDatetime): BaseFreezedPartialShipment
    {
        if ($this instanceof ValidatablePartialShipmentInterface && !$this->isValid()) {
            throw new PartialShipmentCanNotBeFreezedException(
                sprintf('Calling %s::validate() before freezing it is necessary.', get_class($this))
            );
        }

        return new BaseFreezedPartialShipment(
            $this->shipmentItems,
            $this->shippingMethod,
            $this->price,
            $selectedDatetime,
            $this->shippingCategory->getName(),
            $this->description
        );
    }

    public function getItemsMaxSuppliesIn(): int
    {
        return (int) collect($this->shipmentItems)->map(function (PartialShipmentItem $item) {
            return $item->getSuppliesIn();
        })->max();
    }

    public function suppliesInIsZero(): bool
    {
        return 0 === $this->getItemsMaxSuppliesIn();
    }

    public function getSellers(): array
    {
        return collect($this->shipmentItems)
            ->map(fn(PartialShipmentItem $i) => $i->getInventory()->getSeller())
            ->toArray();
    }
}
