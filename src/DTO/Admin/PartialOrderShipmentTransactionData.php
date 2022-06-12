<?php

namespace App\DTO\Admin;

use DateTimeInterface;

class PartialOrderShipmentTransactionData
{
    protected string $trackingNumber;

    protected ?DateTimeInterface $paidAt;

    /**
     * @return string
     */
    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $trackingNumber
     * @return PartialOrderShipmentTransactionData
     */
    public function setTrackingNumber(string $trackingNumber): PartialOrderShipmentTransactionData
    {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getPaidAt(): ?DateTimeInterface
    {
        return $this->paidAt;
    }

    /**
     * @param DateTimeInterface|null $paidAt
     * @return PartialOrderShipmentTransactionData
     */
    public function setPaidAt(?DateTimeInterface $paidAt): PartialOrderShipmentTransactionData
    {
        $this->paidAt = $paidAt;
        return $this;
    }
}
