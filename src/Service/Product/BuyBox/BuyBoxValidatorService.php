<?php

namespace App\Service\Product\BuyBox;

use App\Entity\Inventory;
use App\Entity\Product;

class BuyBoxValidatorService
{
    public function validate(Product $product, Inventory $buyBox): bool
    {
        $oldBuyBox = $product->getBuyBox();

        if (!$oldBuyBox) {
            return true;
        }

        if (!$oldBuyBox->isAvailable()) {
            return true;
        }

        if ($oldBuyBox->getId() === $buyBox->getId()) {
            return false;
        }

        $finalPrice    = $buyBox->getFinalPrice();
        $oldFinalPrice = $oldBuyBox->getFinalPrice();

        if ($buyBox->getHasCampaign() && $finalPrice === $oldFinalPrice) {
            return true;
        }

        return ($oldFinalPrice - $finalPrice) >= $this->minimumDiffPrice($finalPrice);
    }

    protected function minimumDiffPrice(int $finalPrice): int
    {
        switch (true) {
            case $finalPrice < 100_000:
                return 100;
            case $finalPrice >= 100_000 && $finalPrice < 1_000_000:
                return 5_000;
            case $finalPrice >= 1_000_000 && $finalPrice < 30_000_000:
                return 10_000;
            default:
                return 20_000;
        }
    }
}
