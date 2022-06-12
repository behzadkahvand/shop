<?php

namespace App\Service\PartialShipment\ValueObject;

use App\Entity\Order;
use App\Entity\OrderShipment;
use App\Entity\ShippingMethod;

/**
 * Class FreezedPartialShipment
 */
final class FreezedPartialShipment extends BaseFreezedPartialShipment
{
    /**
     * @var array
     */
    private array $categoryDeliveryRange;

    /**
     * @inheritDoc
     */
    public function __construct(
        array $shipmentItems,
        ShippingMethod $shippingMethod,
        PartialShipmentPrice $price,
        \DateTimeInterface $deliveryDate,
        string $title,
        ?string $description = null,
        array $categoryDeliveryRange = []
    ) {
        parent::__construct($shipmentItems, $shippingMethod, $price, $deliveryDate, $title, $description);

        $this->categoryDeliveryRange = $categoryDeliveryRange;
    }

    /**
     * @return array
     */
    public function getCategoryDeliveryRange(): array
    {
        return $this->categoryDeliveryRange;
    }

    /**
     * @inheritDoc
     */
    public function toOrderShipment(Order $order): OrderShipment
    {
        $orderShipment = parent::toOrderShipment($order);
        $orderShipment->setCategoryDeliveryRange($this->categoryDeliveryRange);

        return $orderShipment;
    }
}
