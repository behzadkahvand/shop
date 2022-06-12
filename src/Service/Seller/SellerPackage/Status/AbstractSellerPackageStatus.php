<?php

namespace App\Service\Seller\SellerPackage\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use Closure;

abstract class AbstractSellerPackageStatus
{
    public function support(SellerPackage $sellerPackage): bool
    {
        $sellerOrderItems         = $sellerPackage->getPackageOrderItems();
        $sellerOrderItemCount     = $sellerOrderItems
            ->filter(fn(SellerOrderItem $sellerOrderItem
            ) => !in_array(
                $sellerOrderItem->getStatus(),
                [SellerOrderItemStatus::CANCELED_BY_USER, SellerOrderItemStatus::CANCELED_BY_SELLER]
            ))
            ->count();

        if ($sellerOrderItemCount === 0) {
            return false;
        }

        $filteredSellerItemsCount = $sellerOrderItems->filter($this->getCondition())->count();

        return $this->check($sellerOrderItemCount, $filteredSellerItemsCount);
    }

    abstract public function getName(): string;

    abstract protected function getCondition(): Closure;

    abstract protected function check($sellerOrderItemItemCount, $filteredSellerOrderSellerOrderItemCount): bool;
}
