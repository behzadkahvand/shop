<?php

namespace App\DTO\Admin;

use DateTimeInterface;

class OrderBalanceRefundData
{
    protected string $trackingNumber;

    protected ?DateTimeInterface $paidAt;

    protected ?string $description = null;

    protected ?int $amount = null;

    protected ?bool $force = null;

    /**
     * @return string
     */
    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $trackingNumber
     * @return OrderBalanceRefundData
     */
    public function setTrackingNumber(string $trackingNumber): OrderBalanceRefundData
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
     * @return OrderBalanceRefundData
     */
    public function setPaidAt(?DateTimeInterface $paidAt): OrderBalanceRefundData
    {
        $this->paidAt = $paidAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return OrderBalanceRefundData
     */
    public function setDescription(?string $description): OrderBalanceRefundData
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @param int|null $amount
     * @return OrderBalanceRefundData
     */
    public function setAmount(?int $amount): OrderBalanceRefundData
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getForce(): ?bool
    {
        return $this->force;
    }

    /**
     * @param bool|null $force
     * @return OrderBalanceRefundData
     */
    public function setForce(?bool $force): OrderBalanceRefundData
    {
        $this->force = $force;
        return $this;
    }
}
