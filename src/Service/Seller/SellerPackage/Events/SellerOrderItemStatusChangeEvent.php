<?php

namespace App\Service\Seller\SellerPackage\Events;

use App\Entity\SellerOrderItem;
use Symfony\Contracts\EventDispatcher\Event;

final class SellerOrderItemStatusChangeEvent extends Event
{
    private SellerOrderItem $sellerOrderItem;

    private string $oldStatus;

    private string $newStatus;

    public function __construct(SellerOrderItem $sellerOrderItem, string $oldStatus, string $newStatus)
    {
        $this->sellerOrderItem = $sellerOrderItem;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function getSellerOrderItem(): SellerOrderItem
    {
        return $this->sellerOrderItem;
    }

    public function getOldStatus(): string
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }
}
