<?php

namespace App\Tests\Unit\Service\PartialShipment\Price\Rule\Adapters;

use App\Entity\Inventory;
use App\Service\PartialShipment\Price\Rule\Adapters\ExpressDeliveryPriceRule;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use PHPUnit\Framework\TestCase;

final class ExpressDeliveryPriceRuleTest extends TestCase
{
    public function testItsNotEligibleIfInventoriesCountIsZero()
    {
        $rule = new ExpressDeliveryPriceRule();

        $this->assertFalse($rule->isEligible([], 1000));
    }

    public function testItsNotEligibleIfInventoriesTotalPriceIsLessThanOrEqual200000()
    {
        $shipmentItem = \Mockery::mock(PartialShipmentItem::class);
        $shipmentItem->shouldReceive('getPrice')->once()->andReturn(199000);

        $rule = new ExpressDeliveryPriceRule();

        $this->assertFalse($rule->isEligible([$shipmentItem], 1000));

        $shipmentItem->shouldReceive('getPrice')->once()->andReturn(200000);

        $this->assertFalse($rule->isEligible([$shipmentItem], 1000));
    }

    public function testItIsEligibleIfInventoriesTotalPriceIsMoreThan200000()
    {
        $shipmentItem = \Mockery::mock(PartialShipmentItem::class);
        $shipmentItem->shouldReceive('getPrice')->once()->andReturn(400000);

        $rule = new ExpressDeliveryPriceRule();

        $this->assertTrue($rule->isEligible([$shipmentItem], 1000));
    }

    public function testItMakesGrandTotalZero()
    {
        $inventory = new Inventory();
        $inventory->setFinalPrice(400000);

        $subTotal   = random_int(1000, 20000);

        $rule = new ExpressDeliveryPriceRule();

        $grandTotal = $subTotal + $rule->addToGrandTotal([$inventory], $subTotal);

        $this->assertEquals(0, $grandTotal);
    }
}
