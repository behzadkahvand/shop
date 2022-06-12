<?php

namespace App\Service\ShippingCategory;

use App\Dictionary\ShippingCategoryName;

class CalculateShippingCategoryNameService
{
    /**
     * @param float $weight
     * @param float $length
     * @param float $width
     * @param float $height
     * @return string
     */
    public function calculate(float $weight, float $length, float $width, float $height): string
    {
        if ($weight < 3 && $length < 0.35 && $width < 0.25 && $height < 0.25) {
            return ShippingCategoryName::NORMAL;
        }

        if ($weight > 35 && $length > 0.9 && $width > 0.65 && $height > 0.65) {
            return ShippingCategoryName::SUPER_HEAVY;
        }

        return ShippingCategoryName::HEAVY;
    }
}
