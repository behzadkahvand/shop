<?php

namespace App\Tests\Unit\Service\PartialShipment\Factory\Pipeline\Stages\PartialShipment;

use App\Entity\ShippingCategory;
use App\Entity\Zone;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigurePartialShipmentPayload;
use App\Service\PartialShipment\Factory\Pipeline\Stages\PartialShipment\CalculateDeliveryDatesStage;
use App\Service\PartialShipment\Types\PartialShipment;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class CalculateDeliveryDatesStageTest
 */
final class CalculateDeliveryDatesStageTest extends MockeryTestCase
{
    public function testPayloadAndPriority()
    {
        self::assertEquals(110, CalculateDeliveryDatesStage::getPriority());
        self::assertEquals(ConfigurePartialShipmentPayload::class, CalculateDeliveryDatesStage::getSupportedPayload());
    }

    public function testItCalculatePartialShipmentBaseDeliveryDate(): void
    {
        $baseDeliveryDatetime = new \DateTimeImmutable();
        $shippingCategory     = \Mockery::mock(ShippingCategory::class);
        $zone                 = \Mockery::mock(Zone::class);
        $holidayService       = \Mockery::mock(HolidayServiceInterface::class);
        $partialShipment      = new PartialShipment($shippingCategory, $zone, []);
        $payload              = new ConfigurePartialShipmentPayload($partialShipment, $baseDeliveryDatetime);

        $holidayService->shouldReceive('getFirstOpenShipmentDateSince')
                       ->once()
                       ->with(\Mockery::type(\DateTimeImmutable::class))
                       ->andReturnUsing(fn($dt) => $dt);

        $stage = new CalculateDeliveryDatesStage($holidayService);

        $partialShipment->setBaseDeliveryDate($baseDeliveryDatetime);

        self::assertSame($payload, $stage($payload));

        self::assertEquals(
            $baseDeliveryDatetime->modify('1 day')->format('Y-m-d'),
            $payload->getPartialShipment()->getBaseDeliveryDate()->format('Y-m-d')
        );
    }
}
