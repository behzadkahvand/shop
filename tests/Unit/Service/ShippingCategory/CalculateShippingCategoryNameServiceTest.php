<?php

namespace App\Tests\Unit\Service\ShippingCategory;

use App\Dictionary\ShippingCategoryName;
use App\Service\ShippingCategory\CalculateShippingCategoryNameService;
use PHPUnit\Framework\TestCase;

class CalculateShippingCategoryNameServiceTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testItCalculatesShippingCategoryName($weight, $length, $width, $height, $assert)
    {
        $calculateShippingCategoryName = new CalculateShippingCategoryNameService();

        $result = $calculateShippingCategoryName->calculate($weight, $length, $width, $height);

        self::assertEquals($assert, $result);
    }

    public function provider()
    {
        return [
            [1, 0.25, 0.2, 0.2, ShippingCategoryName::NORMAL],
            [36, 1, 0.66, 0.66, ShippingCategoryName::SUPER_HEAVY],
            [10, 0.65, 0.45, 0.45, ShippingCategoryName::HEAVY],
        ];
    }
}
