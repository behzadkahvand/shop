<?php

namespace App\Service\Seller\SellerPackage\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerPackageStatus;
use App\Entity\SellerOrderItem;
use Closure;

final class SentSellerPackageStatus extends AbstractSellerPackageStatus
{
    public function getName(): string
    {
        return SellerPackageStatus::SENT;
    }

    protected function getCondition(): Closure
    {
        return fn(SellerOrderItem $sellerOrderItem) => $sellerOrderItem->getStatus() === SellerOrderItemStatus::SENT_BY_SELLER;
    }

    protected function check($sellerOrderItemItemCount, $filteredSellerOrderSellerOrderItemCount): bool
    {
        return $sellerOrderItemItemCount === $filteredSellerOrderSellerOrderItemCount;
    }
}
