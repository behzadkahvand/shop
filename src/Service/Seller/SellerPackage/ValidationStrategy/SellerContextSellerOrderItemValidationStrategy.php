<?php

namespace App\Service\Seller\SellerPackage\ValidationStrategy;

use App\Dictionary\OrderStatus;
use App\Entity\SellerOrderItem;
use App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderStatusException;
use App\Service\Seller\SellerOrderItem\Exceptions\SellerOrderItemCanNotBePackagedException;

/**
 * Class SellerContextSellerOrderItemValidationStrategy
 */
final class SellerContextSellerOrderItemValidationStrategy implements SellerOrderItemValidationStrategyInterface
{
    /**
     * @inheritDoc
     */
    public function validate(iterable $sellerOrderItems): void
    {
        foreach ($sellerOrderItems as $sellerOrderItem) {
            if ($this->getOrderStatus($sellerOrderItem) !== OrderStatus::CONFIRMED) {
                throw new InvalidSellerOrderStatusException();
            }

            if (!$sellerOrderItem->isWaitingForSend()) {
                throw new SellerOrderItemCanNotBePackagedException();
            }
        }
    }

    private function getOrderStatus(SellerOrderItem $sellerOrderItem): ?string
    {
        return $sellerOrderItem->getOrderItem()?->getOrder()?->getStatus();
    }
}
