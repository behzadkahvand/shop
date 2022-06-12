<?php

namespace App\Tests\Unit\Service\PartialShipment\Factory\Pipeline\Stages;

use App\Entity\Seller;
use App\Entity\Zone;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\Factory\Pipeline\Payload\CreatePartialShipmentPayload;
use App\Service\PartialShipment\Factory\Pipeline\Stages\CalculateBaseDeliveryDateStage;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class CalculateBaseDeliveryDateTest
 */
final class CalculateBaseDeliveryDateTest extends MockeryTestCase
{
    public function testPayloadAndPriority()
    {
        self::assertEquals(100, CalculateBaseDeliveryDateStage::getPriority());
        self::assertEquals(CreatePartialShipmentPayload::class, CalculateBaseDeliveryDateStage::getSupportedPayload());
    }

    public function testItApplyShipmentHolidays()
    {
        $partialShipment = \Mockery::mock(AbstractPartialShipment::class);
        $partialShipment->shouldReceive('getItemsMaxSuppliesIn')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(0);
        $partialShipment->shouldReceive('getSellers')
                        ->once()
                        ->withNoArgs()
                        ->andReturn([]);
        $partialShipment->shouldReceive('setBaseDeliveryDate')
                        ->once()
                        ->with(\Mockery::type(\DateTimeImmutable::class))
                        ->andReturn();

        $zone                 = \Mockery::mock(Zone::class);
        $baseShipmentDateTime = new \DateTimeImmutable();
        $payload              = new CreatePartialShipmentPayload($partialShipment, $zone, $baseShipmentDateTime);

        $baseDeliveryDate = $baseShipmentDateTime->modify('1 day');

        $holidayService = \Mockery::mock(HolidayServiceInterface::class);
        $holidayService->shouldReceive('getFirstOpenSupplyDateSince')
                       ->once()
                       ->with(\Mockery::type(\DateTimeImmutable::class))
                       ->andReturnUsing(fn($baseDeliveryDate) => $baseDeliveryDate);
        $holidayService->shouldReceive('getFirstOpenShipmentDateSince')
                       ->once()
                       ->with(\Mockery::type(\DateTimeImmutable::class))
                       ->andReturn($baseDeliveryDate);

        $stage = new CalculateBaseDeliveryDateStage($holidayService);

        self::assertSame($payload, $stage($payload));
    }

    public function testItApplySupplyHolidaysWithPositiveSuppliesIn()
    {
        $partialShipment = new class extends AbstractPartialShipment {
            public function __construct()
            {
            }

            public function getItemsMaxSuppliesIn(): int
            {
                return 1;
            }

            public function getSellers(): array
            {
                return [
                    \Mockery::mock(Seller::class),
                ];
            }
        };

        $zone          = \Mockery::mock(Zone::class);
        $orderDatetime = new \DateTimeImmutable();
        $payload       = new CreatePartialShipmentPayload($partialShipment, $zone, $orderDatetime);

        $holidayService = \Mockery::mock(HolidayServiceInterface::class);
        $holidayService->shouldReceive('isOpenForSupply')
                       ->once()
                       ->with(\Mockery::type(\DateTimeImmutable::class))
                       ->andReturnTrue();
        $holidayService->shouldReceive('isOpenForSupply')
                       ->once()
                       ->with(\Mockery::type(\DateTimeImmutable::class), \Mockery::type(Seller::class))
                       ->andReturnTrue();

        $holidayService->shouldReceive('getFirstOpenSupplyDateSince')
                       ->once()
                       ->with(\Mockery::type(\DateTimeImmutable::class), \Mockery::type(Seller::class))
                       ->andReturnUsing(fn($baseDeliveryDate) => $baseDeliveryDate);
        $holidayService->shouldReceive('getFirstOpenShipmentDateSince')
                       ->once()
                       ->with(\Mockery::type(\DateTimeImmutable::class), \Mockery::type(Seller::class))
                       ->andReturnUsing(fn($date) => $date);

        $stage = new CalculateBaseDeliveryDateStage($holidayService);

        self::assertSame($payload, $stage($payload));
        self::assertNotEquals(
            $orderDatetime->format('Y-m-d'),
            $partialShipment->getBaseDeliveryDate()->format('Y-m-d')
        );
        self::assertEquals(
            $orderDatetime->modify('1 day')->format('Y-m-d'),
            $partialShipment->getBaseDeliveryDate()->format('Y-m-d')
        );
    }

    public function testItApplySupplyHolidaysWithZeroSuppliesIn(): void
    {
        $partialShipment = new class extends AbstractPartialShipment {
            public function __construct()
            {
            }

            public function getItemsMaxSuppliesIn(): int
            {
                return 0;
            }

            public function getSellers(): array
            {
                return [];
            }
        };

        $zone          = \Mockery::mock(Zone::class);
        $orderDatetime = new \DateTimeImmutable();
        $payload       = new CreatePartialShipmentPayload($partialShipment, $zone, $orderDatetime);

        $holidayService = \Mockery::mock(HolidayServiceInterface::class);
        $holidayService->shouldNotReceive('isOpenForSupply');

        $holidayService->shouldReceive('getFirstOpenSupplyDateSince')
                       ->once()
                       ->with(\Mockery::type(\DateTimeImmutable::class))
                       ->andReturnUsing(fn($baseDeliveryDate) => $baseDeliveryDate);

        $holidayService->shouldReceive('getFirstOpenShipmentDateSince')
                       ->once()
                       ->with(\Mockery::type(\DateTimeImmutable::class))
                       ->andReturnUsing(fn($date) => $date);

        $stage = new CalculateBaseDeliveryDateStage($holidayService);

        self::assertSame($payload, $stage($payload));
        self::assertEquals(
            $orderDatetime->format('Y-m-d'),
            $partialShipment->getBaseDeliveryDate()->format('Y-m-d')
        );
    }
}
