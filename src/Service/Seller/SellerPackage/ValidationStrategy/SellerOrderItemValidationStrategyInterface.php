<?php

namespace App\Service\Seller\SellerPackage\ValidationStrategy;

/**
 * Interface SellerOrderItemValidationStrategyInterface
 */
interface SellerOrderItemValidationStrategyInterface
{
    /**
     * @param iterable $sellerOrderItems
     */
    public function validate(iterable $sellerOrderItems): void;
}
