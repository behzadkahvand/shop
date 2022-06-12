<?php

namespace App\Service\Product\Availability;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Entity\Product;

/**
 * Class ProductAvailabilityChecker
 */
class ProductAvailabilityChecker
{
    /**
     * @var iterable|ProductAvailabilityByInventoryCheckerInterface[]
     */
    private iterable $availabilityCheckers;

    /**
     * ProductAvailabilityChecker constructor.
     *
     * @param iterable $availabilityCheckers
     */
    public function __construct(iterable $availabilityCheckers)
    {
        $this->availabilityCheckers = $availabilityCheckers;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function isAvailable(Product $product): bool
    {
        return ProductStatusDictionary::UNAVAILABLE !== $product->getStatus();
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function shouldBeAvailable(Product $product): bool
    {
        foreach ($product->getProductVariants() as $variant) {
            foreach ($variant->getInventories() as $inventory) {
                foreach ($this->availabilityCheckers as $availabilityChecker) {
                    if ($availabilityChecker->productShouldBeUnavailable($inventory)) {
                        continue 2; // if any of checkers vote for unavailability we skip inventory an check next one
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function shouldBeUnavailable(Product $product): bool
    {
        foreach ($product->getProductVariants() as $variant) {
            foreach ($variant->getInventories() as $inventory) {
                foreach ($this->availabilityCheckers as $availabilityChecker) {
                    if ($availabilityChecker->productShouldBeUnavailable($inventory)) {
                        continue 2;  // if any of checkers vote for unavailability we skip inventory an check next one
                    }
                }

                return false;
            }
        }

        return true;
    }

    /**
     * @param Inventory $inventory
     *
     * @return bool
     */
    public function inventoryIsEligibleToChangeProductAvailability(Inventory $inventory): bool
    {
        if ($this->isAvailable($inventory->getVariant()->getProduct())) {
            foreach ($this->availabilityCheckers as $availabilityChecker) {
                if ($availabilityChecker->productShouldBeUnavailable($inventory)) {
                    return true;
                }
            }

            return false;
        }

        foreach ($this->availabilityCheckers as $availabilityChecker) {
            if ($availabilityChecker->productShouldBeAvailable($inventory)) {
                return true;
            }
        }

        return false;
    }
}
