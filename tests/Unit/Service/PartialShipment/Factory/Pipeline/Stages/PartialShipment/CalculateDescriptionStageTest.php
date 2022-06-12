<?php

namespace App\Tests\Unit\Service\PartialShipment\Factory\Pipeline\Stages\PartialShipment;

use App\Entity\Delivery;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigurePartialShipmentPayload;
use App\Service\PartialShipment\Factory\Pipeline\Stages\PartialShipment\CalculateDescriptionStage;
use App\Service\PartialShipment\Types\PartialShipment;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CalculateDescriptionStageTest
 */
final class CalculateDescriptionStageTest extends MockeryTestCase
{
    public function testPayloadAndPriority()
    {
        self::assertEquals(100, CalculateDescriptionStage::getPriority());
        self::assertEquals(ConfigurePartialShipmentPayload::class, CalculateDescriptionStage::getSupportedPayload());
    }

    public function testItCalculatePartialShipmentDescription()
    {
        $baseDeliveryDatetime = new \DateTimeImmutable();

        $delivery = \Mockery::mock(Delivery::class);
        $delivery->shouldReceive('getStart')->once()->withNoArgs()->andReturn(1);
        $delivery->shouldReceive('getEnd')->once()->withNoArgs()->andReturn(2);

        $description = 'بین ۱ تا ۲ روز کاری';

        $translator = \Mockery::mock(TranslatorInterface::class);
        $translator->shouldReceive('trans')
                   ->once()
                   ->with('non_express_partial_shipment_description', ['start' => 1, 'end' => 2], 'shipment', 'fa')
                   ->andReturn($description);

        $partialShipment = \Mockery::mock(PartialShipment::class);
        $partialShipment->shouldReceive('getBaseDeliveryDate')->once()->withNoArgs()->andReturn($baseDeliveryDatetime);
        $partialShipment->shouldReceive('getShippingCategory->getDelivery')->once()->withNoArgs()->andReturn($delivery);
        $partialShipment->shouldReceive('setDeliveryRange')
                        ->once()
                        ->with([1, 2])
                        ->andReturn();
        $partialShipment->shouldReceive('setDescription')
                        ->once()
                        ->with($description)
                        ->andReturn();

        $payload              = new ConfigurePartialShipmentPayload($partialShipment, $baseDeliveryDatetime);

        $stage = new CalculateDescriptionStage($translator);

        self::assertSame($payload, $stage($payload));
    }
}
