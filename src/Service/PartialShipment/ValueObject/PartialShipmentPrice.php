<?php

namespace App\Service\PartialShipment\ValueObject;

/**
 * Class PartialShipmentPrice
 */
final class PartialShipmentPrice implements \JsonSerializable
{
    /**
     * @var int
     */
    private int $subTotal;

    /**
     * @var int
     */
    private int $grandTotal;

    /**
     * PartialShipmentPrice constructor.
     *
     * @param int $subTotal
     * @param int $grandTotal
     */
    public function __construct(int $subTotal, int $grandTotal)
    {
        $this->subTotal = $subTotal;
        $this->grandTotal = $grandTotal;
    }

    /**
     * @return int
     */
    public function getSubTotal(): int
    {
        return $this->subTotal;
    }

    /**
     * @return int
     */
    public function getGrandTotal(): int
    {
        return $this->grandTotal;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
