<?php

namespace App\Tests\Unit\Service\PartialShipment\Price;

use App\Entity\Inventory;
use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Entity\ShippingMethodPrice;
use App\Entity\Zone;
use App\Repository\ShippingMethodPriceRepository;
use App\Service\PartialShipment\Exceptions\MinimumShipmentItemCountException;
use App\Service\PartialShipment\Price\PartialShipmentShippingMethodAndPriceCalculator;
use App\Service\PartialShipment\Price\Rule\PartialShipmentPriceRuleInterface;
use App\Service\PartialShipment\ValueObject\PartialShipmentPrice;
use App\Service\PartialShipment\ValueObject\PartialShipmentShippingMethodAndPriceCalculationResult;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class PartialShipmentShippingMethodAndPriceCalculatorTest extends MockeryTestCase
{
    public function testItThrowExceptionIfNoInventoryIsPassed()
    {
        $calculator = new PartialShipmentShippingMethodAndPriceCalculator(
            \Mockery::mock(ShippingMethodPriceRepository::class),
            []
        );

        $this->expectException(MinimumShipmentItemCountException::class);

        $calculator->calculate(
            \Mockery::mock(ShippingCategory::class),
            \Mockery::mock(Zone::class),
            []
        );
    }

    public function testItCalculateShippingMethodAndPrice()
    {
        $subTotal       = 1000;
        $grandTotal     = 1000;
        $category       = \Mockery::mock(ShippingCategory::class);
        $zone           = \Mockery::mock(Zone::class);
        $shippingMethod = \Mockery::mock(ShippingMethod::class);

        $shippingMethodPrice = \Mockery::mock(ShippingMethodPrice::class);
        $shippingMethodPrice->shouldReceive('getShippingMethod')->once()->withNoArgs()->andReturn($shippingMethod);
        $shippingMethodPrice->shouldReceive('getPrice')->once()->withNoArgs()->andReturn($subTotal);

        $inventories = [\Mockery::mock(Inventory::class)];

        $rule = \Mockery::mock(PartialShipmentPriceRuleInterface::class);
        $rule->shouldReceive('isEligible')->once()->with($inventories, $subTotal)->andReturnTrue();
        $rule->shouldReceive('addToGrandTotal')->once()->with($inventories, $subTotal)->andReturn(0);

        $rules = [$rule];

        $repository = \Mockery::mock(ShippingMethodPriceRepository::class);
        $repository->shouldReceive('getPriceByShippingCategoryAndZone')
                   ->once()
                   ->with($category, $zone)
                   ->andReturn($shippingMethodPrice);

        $calculator = new PartialShipmentShippingMethodAndPriceCalculator($repository, $rules);

        $result = $calculator->calculate($category, $zone, $inventories);

        $this->assertInstanceOf(PartialShipmentShippingMethodAndPriceCalculationResult::class, $result);
        $this->assertSame($shippingMethod, $result->getShippingMethod());
        $this->assertInstanceOf(PartialShipmentPrice::class, $result->getPrice());
        $this->assertEquals($subTotal, $result->getPrice()->getSubTotal());
        $this->assertEquals($grandTotal, $result->getPrice()->getGrandTotal());
    }
}
