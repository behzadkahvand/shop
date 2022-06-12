<?php

namespace App\DTO\Admin;

use App\Dictionary\OrderStatus;
use App\Entity\OrderCancelReason;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChangeOrderStatusData
 */
final class ChangeOrderStatusData
{
    /**
     * @var bool|null
     *
     * @Assert\NotBlank(allowNull=true)
     * @Assert\Expression(
     *     "(this.getStatus() !== 'CANCELED' or this.getForce() === true) and (this.getStatus() !== 'DELIVERED' or this.getForce() === true)",
     *     message="Order status must be forced for changing status to delivered or canceled. force key must be set true"
     * )
     */
    private ?bool $force = null;

    /**
     * @var string|null
     *
     * @Assert\Choice(callback="getChoices")
     * @Assert\NotBlank()
     */
    private ?string $status = null;

    /**
     * @var OrderCancelReason|null
     */
    private ?OrderCancelReason $cancelReason = null;

    /**
     * @return bool|null
     */
    public function getForce(): ?bool
    {
        return $this->force;
    }

    /**
     * @param bool|null $force
     *
     * @return ChangeOrderStatusData
     */
    public function setForce(?bool $force): ChangeOrderStatusData
    {
        $this->force = $force;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     *
     * @return ChangeOrderStatusData
     */
    public function setStatus(?string $status): ChangeOrderStatusData
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return OrderCancelReason|null
     */
    public function getCancelReason(): ?OrderCancelReason
    {
        return $this->cancelReason;
    }

    /**
     * @param OrderCancelReason|null $cancelReason
     *
     * @return ChangeOrderStatusData
     */
    public function setCancelReason(?OrderCancelReason $cancelReason): ChangeOrderStatusData
    {
        $this->cancelReason = $cancelReason;

        return $this;
    }

    /**
     * @return array
     */
    public function getChoices(): array
    {
        return [
            OrderStatus::WAIT_CUSTOMER,
            OrderStatus::CALL_FAILED,
            OrderStatus::CONFIRMED,
            OrderStatus::DELIVERED,
            OrderStatus::CANCELED,
            OrderStatus::REFUND,
        ];
    }
}
