<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Entity\SellerOrderItem;

/**
 * Class CanceledBySellerSellerOrderItemStatus
 */
final class CanceledBySellerSellerOrderItemStatus extends AbstractSellerOrderItemStatus
{
    /*public function waitingForSend(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function sentBySeller(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function received(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function fulfilling(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function missed(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function damaged(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function storaged(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function canceledByUser(SellerOrderItem $sellerOrderItem): void
    {
 $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function canceledBySeller(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function returning(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function returned(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function sentToCustomer(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    public function delivered(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }*/

    /**
     * @inheritDoc
     */
    protected function getName(): string
    {
        return SellerOrderItemStatus::CANCELED_BY_SELLER;
    }
}
