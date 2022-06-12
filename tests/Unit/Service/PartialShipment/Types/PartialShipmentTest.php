<?php

namespace App\Tests\Unit\Service\PartialShipment\Types;

use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Entity\ShippingPeriod;
use App\Entity\Zone;
use App\Service\PartialShipment\Types\PartialShipment;
use App\Service\PartialShipment\ValueObject\ExpressPartialDeliveryDate;
use App\Service\PartialShipment\ValueObject\FreezedPartialShipment;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\PartialShipment\ValueObject\PartialShipmentPeriod;
use App\Service\PartialShipment\ValueObject\PartialShipmentPrice;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class PartialShipmentTest
 */
final class PartialShipmentTest extends MockeryTestCase
{
    public function testSettingAndGettingDeliveryRange(): void
    {
        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $zone = Mockery::mock(Zone::class);
        $partialShipment = new PartialShipment($shippingCategory, $zone, []);

        $deliveryRange = [1, 5];
        $partialShipment->setDeliveryRange($deliveryRange);
        self::assertEquals($deliveryRange, $partialShipment->getDeliveryRange());
    }

    public function testGettingSuppliesIn(): void
    {
        $itemsMaxSuppliesIn = 1;

        $item = Mockery::mock(PartialShipmentItem::class);
        $item->shouldReceive('getSuppliesIn')->once()->withNoArgs()->andReturn($itemsMaxSuppliesIn);
        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $zone = Mockery::mock(Zone::class);
        $partialShipment = new PartialShipment($shippingCategory, $zone, [$item]);

        self::assertEquals($itemsMaxSuppliesIn, $partialShipment->getItemsMaxSuppliesIn());
    }

    public function testItFreeze(): void
    {
        $baseDeliveryDate = new \DateTimeImmutable();

        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $shippingCategory->shouldReceive('getName')->once()->withNoArgs()->andReturn('name');

        $item = Mockery::mock(PartialShipmentItem::class);
        $item->shouldReceive('getShippingCategory')->twice()->withNoArgs()->andReturn($shippingCategory);

        $shippingPeriod1 = Mockery::mock(ShippingPeriod::class);
        $shippingPeriod1->shouldReceive(['getId' => 1, 'getStart' => $baseDeliveryDate, 'getEnd' => $baseDeliveryDate])
                        ->once()
                        ->withNoArgs();

        $shippingPeriod2 = Mockery::mock(ShippingPeriod::class);
        $shippingPeriod2->shouldReceive([
                            'getId' => 1,
                            'getStart' => $baseDeliveryDate->modify('1 day'),
                            'getEnd' => $baseDeliveryDate->modify('1 day')
                        ])
                        ->once()
                        ->withNoArgs();

        $partialShipment = new PartialShipment($shippingCategory, Mockery::mock(Zone::class), [$item]);
        $partialShipment->setBaseDeliveryDate($baseDeliveryDate);
        $partialShipment->setShippingMethod(Mockery::mock(ShippingMethod::class));
        $partialShipment->setPrice(new PartialShipmentPrice(1000, 1000));
        $partialShipment->setDeliveryRange([1, 5]);
        $partialShipment->setCalculatedDeliveryDates([
            new ExpressPartialDeliveryDate(
                $baseDeliveryDate,
                [new PartialShipmentPeriod($shippingPeriod1)]
            ),
            new ExpressPartialDeliveryDate(
                $baseDeliveryDate->modify('1 day'),
                [new PartialShipmentPeriod($shippingPeriod2)]
            ),
        ]);

        self::assertInstanceOf(FreezedPartialShipment::class, $partialShipment->freeze($baseDeliveryDate));
    }
}
