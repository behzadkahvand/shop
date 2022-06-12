<?php

namespace App\Tests\Unit\Service\PartialShipment\Factory\Pipeline\Stages;

use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Entity\Zone;
use App\Service\PartialShipment\Factory\Pipeline\Payload\CreatePartialShipmentPayload;
use App\Service\PartialShipment\Factory\Pipeline\Stages\CalculateShippingMethodAndPriceStage;
use App\Service\PartialShipment\Price\PartialShipmentShippingMethodAndPriceCalculator;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\PartialShipment\ValueObject\PartialShipmentPrice;
use App\Service\PartialShipment\ValueObject\PartialShipmentShippingMethodAndPriceCalculationResult;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class CalculateShippingMethodAndPriceStageTest
 */
final class CalculateShippingMethodAndPriceStageTest extends MockeryTestCase
{
    public function testPayloadAndPriority()
    {
        self::assertEquals(90, CalculateShippingMethodAndPriceStage::getPriority());
        self::assertEquals(CreatePartialShipmentPayload::class, CalculateShippingMethodAndPriceStage::getSupportedPayload());
    }

    public function testItCalculatePartialShipmentPrice()
    {
        $price            = new PartialShipmentPrice(0, 0);
        $shippingMethod   = \Mockery::mock(ShippingMethod::class);
        $items            = [\Mockery::mock(PartialShipmentItem::class)];
        $shippingCategory = \Mockery::mock(ShippingCategory::class);

        $partialShipment = \Mockery::mock(AbstractPartialShipment::class);
        $partialShipment->shouldReceive('getShipmentItems')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($items);
        $partialShipment->shouldReceive('getShippingCategory')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($shippingCategory);
        $partialShipment->shouldReceive('setShippingMethod')
                        ->once()
                        ->with($shippingMethod)
                        ->andReturn($shippingMethod);
        $partialShipment->shouldReceive('setPrice')
                        ->once()
                        ->with($price)
                        ->andReturn($price);

        $zone    = \Mockery::mock(Zone::class);
        $payload = new CreatePartialShipmentPayload($partialShipment, $zone, new \DateTimeImmutable());

        $calculationResult = \Mockery::mock(PartialShipmentShippingMethodAndPriceCalculationResult::class);
        $calculationResult->shouldReceive('getShippingMethod')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($shippingMethod);
        $calculationResult->shouldReceive('getPrice')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($price);

        $priceCalculator = \Mockery::mock(PartialShipmentShippingMethodAndPriceCalculator::class);
        $priceCalculator->shouldReceive('calculate')
                        ->once()
                        ->with($shippingCategory, $zone, $items)
                        ->andReturn($calculationResult);

        $stage = new CalculateShippingMethodAndPriceStage($priceCalculator);

        self::assertSame($payload, $stage($payload));
    }
}
