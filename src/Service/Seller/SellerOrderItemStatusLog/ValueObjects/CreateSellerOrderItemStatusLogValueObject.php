<?php

namespace App\Service\Seller\SellerOrderItemStatusLog\ValueObjects;

use App\Entity\SellerOrderItem;

class CreateSellerOrderItemStatusLogValueObject
{
    protected ?SellerOrderItem $sellerOrderItem;

    protected ?string $statusFrom;

    protected ?string $statusTo;

    public function __construct(
        SellerOrderItem $sellerOrderItem = null,
        string $statusFrom = null,
        string $statusTo = null
    ) {
        $this->sellerOrderItem = $sellerOrderItem;
        $this->statusFrom      = $statusFrom;
        $this->statusTo        = $statusTo;
    }

    public function getSellerOrderItem(): SellerOrderItem
    {
        return $this->sellerOrderItem;
    }

    public function setSellerOrderItem(SellerOrderItem $sellerOrderItem)
    {
        $this->sellerOrderItem = $sellerOrderItem;

        return $this;
    }

    public function getStatusFrom(): string
    {
        return $this->statusFrom;
    }

    public function setStatusFrom(string $statusFrom): CreateSellerOrderItemStatusLogValueObject
    {
        $this->statusFrom = $statusFrom;

        return $this;
    }

    public function getStatusTo(): string
    {
        return $this->statusTo;
    }

    public function setStatusTo(string $statusTo): CreateSellerOrderItemStatusLogValueObject
    {
        $this->statusTo = $statusTo;

        return $this;
    }
}
