<?php

namespace App\Service\PartialShipment\Types;

use App\Service\PartialShipment\ValueObject\BaseFreezedPartialShipment;
use App\Service\PartialShipment\ValueObject\FreezedPartialShipment;
use DateTimeInterface;

/**
 * Class PartialShipment
 */
class PartialShipment extends AbstractPartialShipment
{
    private array $deliveryRange;

    public function getDeliveryRange(): array
    {
        return $this->deliveryRange;
    }

    public function setDeliveryRange(array $deliveryRange): void
    {
        $this->deliveryRange = $deliveryRange;
    }

    public function freeze(DateTimeInterface $selectedDatetime): BaseFreezedPartialShipment
    {
        $freezed = parent::freeze($this->getBaseDeliveryDate());

        return new FreezedPartialShipment(
            $freezed->getShipmentItems(),
            $freezed->getShippingMethod(),
            $freezed->getPrice(),
            $freezed->getDeliveryDate(),
            $freezed->getTitle(),
            $freezed->getDescription(),
            $this->deliveryRange
        );
    }
}
