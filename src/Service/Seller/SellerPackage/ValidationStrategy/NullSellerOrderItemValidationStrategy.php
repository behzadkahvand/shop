<?php

namespace App\Service\Seller\SellerPackage\ValidationStrategy;

/**
 * Class NullSellerOrderItemValidationStrategy
 */
final class NullSellerOrderItemValidationStrategy implements SellerOrderItemValidationStrategyInterface
{
    /**
     * @inheritDoc
     */
    public function validate(iterable $sellerOrderItems): void
    {
    }
}
