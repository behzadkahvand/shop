<?php

namespace App\Service\OrderStatusLog\ValueObjects;

use App\Entity\Admin;
use App\Entity\Order;

class CreateOrderStatusLogValueObject
{
    protected ?Order $order;

    protected ?string $statusFrom;

    protected ?string $statusTo;

    protected ?Admin $user;

    /**
     * CreateOrderStatusLogValueObject constructor.
     *
     * @param Order $order
     * @param string $statusFrom
     * @param string $statusTo
     * @param Admin|null $user
     */
    public function __construct(Order $order = null, string $statusFrom = null, string $statusTo = null, ?Admin $user = null)
    {
        $this->order      = $order;
        $this->statusFrom = $statusFrom;
        $this->statusTo   = $statusTo;
        $this->user       = $user;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return CreateOrderStatusLogValueObject
     */
    public function setOrder(Order $order): CreateOrderStatusLogValueObject
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusFrom(): string
    {
        return $this->statusFrom;
    }

    /**
     * @param string $statusFrom
     * @return CreateOrderStatusLogValueObject
     */
    public function setStatusFrom(string $statusFrom): CreateOrderStatusLogValueObject
    {
        $this->statusFrom = $statusFrom;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusTo(): string
    {
        return $this->statusTo;
    }

    /**
     * @param string $statusTo
     * @return CreateOrderStatusLogValueObject
     */
    public function setStatusTo(string $statusTo): CreateOrderStatusLogValueObject
    {
        $this->statusTo = $statusTo;
        return $this;
    }

    /**
     * @return Admin|null
     */
    public function getUser(): ?Admin
    {
        return $this->user;
    }

    /**
     * @param Admin|null $user
     * @return CreateOrderStatusLogValueObject
     */
    public function setUser(?Admin $user): CreateOrderStatusLogValueObject
    {
        $this->user = $user;
        return $this;
    }
}
