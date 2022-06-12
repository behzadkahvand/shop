<?php

namespace App\Service\PartialShipment\ValueObject;

use App\Entity\ShippingMethod;

/**
 * Class PartialShipmentPrice
 */
class PartialShipmentShippingMethodAndPriceCalculationResult
{
    /**
     * @var ShippingMethod
     */
    private ShippingMethod $shippingMethod;
    /**
     * @var PartialShipmentPrice
     */
    private PartialShipmentPrice $price;

    /**
     * PartialShipmentPrice constructor.
     *
     * @param ShippingMethod $shippingMethod
     * @param PartialShipmentPrice $price
     */
    public function __construct(ShippingMethod $shippingMethod, PartialShipmentPrice $price)
    {
        $this->shippingMethod = $shippingMethod;
        $this->price = $price;
    }

    /**
     * @return ShippingMethod
     */
    public function getShippingMethod(): ShippingMethod
    {
        return $this->shippingMethod;
    }

    /**
     * @return PartialShipmentPrice
     */
    public function getPrice(): PartialShipmentPrice
    {
        return $this->price;
    }
}
