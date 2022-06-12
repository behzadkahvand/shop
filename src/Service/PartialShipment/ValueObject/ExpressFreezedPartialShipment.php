<?php

namespace App\Service\PartialShipment\ValueObject;

use App\Entity\Order;
use App\Entity\OrderShipment;
use App\Entity\ShippingMethod;
use App\Entity\ShippingPeriod;
use DateTimeInterface;

/**
 * Class ExpressFreezedPartialShipment
 */
class ExpressFreezedPartialShipment extends BaseFreezedPartialShipment
{
    private ShippingPeriod $shippingPeriod;

    /**
     * ExpressFreezedPartialShipment constructor.
     *
     * @param array $shipmentItems
     * @param ShippingMethod $shippingMethod
     * @param PartialShipmentPrice $price
     * @param DateTimeInterface $deliveryDate
     * @param string $title
     * @param ShippingPeriod $period
     */
    public function __construct(
        array $shipmentItems,
        ShippingMethod $shippingMethod,
        PartialShipmentPrice $price,
        DateTimeInterface $deliveryDate,
        string $title,
        ShippingPeriod $period
    ) {
        parent::__construct($shipmentItems, $shippingMethod, $price, $deliveryDate, $title);

        $this->shippingPeriod = $period;
    }

    /**
     * @return ShippingPeriod
     */
    public function getShippingPeriod(): ShippingPeriod
    {
        return $this->shippingPeriod;
    }

    /**
     * @param Order $order
     *
     * @return OrderShipment
     */
    public function toOrderShipment(Order $order): OrderShipment
    {
        $orderShipment = parent::toOrderShipment($order);
        $orderShipment->setPeriod($this->shippingPeriod);

        return $orderShipment;
    }
}
