<?php

namespace App\Service\Seller\SellerPackage\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerPackageStatus;
use App\Entity\SellerOrderItem;
use Closure;

final class PartialReceivedSellerPackageStatus extends AbstractSellerPackageStatus
{
    public function getName(): string
    {
        return SellerPackageStatus::PARTIAL_RECEIVED;
    }

    protected function getCondition(): Closure
    {
        return fn(SellerOrderItem $sellerOrderItem) => in_array($sellerOrderItem->getStatus(), [
            SellerOrderItemStatus::FULFILLING,
            SellerOrderItemStatus::SENT_TO_CUSTOMER,
            SellerOrderItemStatus::DELIVERED,
            SellerOrderItemStatus::RECEIVED,
            SellerOrderItemStatus::STORAGED,
        ]);
    }

    protected function check($sellerOrderItemCount, $filteredSellerOrderItemCount): bool
    {
        return $sellerOrderItemCount > $filteredSellerOrderItemCount && $filteredSellerOrderItemCount > 0;
    }
}
