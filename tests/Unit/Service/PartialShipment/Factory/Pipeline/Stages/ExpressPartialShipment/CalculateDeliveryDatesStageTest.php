<?php

namespace App\Tests\Unit\Service\PartialShipment\Factory\Pipeline\Stages\ExpressPartialShipment;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\ShippingCategory;
use App\Entity\ShippingPeriod;
use App\Entity\Zone;
use App\Repository\ShippingPeriodRepository;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\Factory\Calculators\Express\ExpressShipmentDeliveryDateAndPeriodCalculatorFactory;
use App\Service\PartialShipment\Factory\Calculators\Express\ExpressShipmentDeliveryDateAndPeriodCalculatorInterface;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigureExpressPartialShipmentPayload;
use App\Service\PartialShipment\Factory\Pipeline\Stages\ExpressPartialShipment\CalculateDeliveryDatesStage;
use App\Service\PartialShipment\Types\ExpressPartialShipment;
use App\Service\PartialShipment\ValueObject\DeliveryDateAndPeriodCalculatorResult;
use App\Service\PartialShipment\ValueObject\ExpressPartialDeliveryDate;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\PartialShipment\ValueObject\PartialShipmentPeriod;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class CalculateDeliveryDatesStageTest
 */
final class CalculateDeliveryDatesStageTest extends MockeryTestCase
{
    public function testPayloadAndPriority(): void
    {
        self::assertEquals(100, CalculateDeliveryDatesStage::getPriority());
        self::assertEquals(
            ConfigureExpressPartialShipmentPayload::class,
            CalculateDeliveryDatesStage::getSupportedPayload()
        );
    }

    public function testItCalculateDeliveryDates(): void
    {
        $item                 = Mockery::mock(PartialShipmentItem::class);
        $orderDateTime        = new DateTimeImmutable();
        $baseShipmentDateTime = clone $orderDateTime;
        $partialShipment      = new ExpressPartialShipment(
            Mockery::mock(ShippingCategory::class),
            Mockery::mock(Zone::class),
            [$item]
        );

        $partialShipment->setBaseDeliveryDate($baseShipmentDateTime);

        $payload = new ConfigureExpressPartialShipmentPayload($partialShipment, $orderDateTime);

        $holidayService = Mockery::mock(HolidayServiceInterface::class);
        $holidayService->shouldReceive('getFirstOpenShipmentDateSince')
                       ->between(3, 5)
                       ->with(Mockery::type(DateTimeImmutable::class))
                       ->andReturnUsing(fn(DateTimeImmutable $datetime) => $datetime);

        $periods = $this->getPeriodsMocks();

        $shippingPeriodRepository = Mockery::mock(ShippingPeriodRepository::class);
        $shippingPeriodRepository->shouldReceive('findBy')
                                 ->once()
                                 ->with(['isActive' => true], ['start' => 'ASC'])
                                 ->andReturn($periods);

        $configService = \Mockery::mock(ConfigurationServiceInterface::class);
        $configService->shouldReceive('findByCode')
                      ->once()
                      ->with(ConfigurationCodeDictionary::PARTIAL_SHIPMENT_SELECTABLE_DAYS_COUNT)
                      ->andReturnNull();

        $factory = \Mockery::mock(ExpressShipmentDeliveryDateAndPeriodCalculatorFactory::class);
        $factory->shouldReceive('create')->once()->with($payload)->andReturn(
            new class implements ExpressShipmentDeliveryDateAndPeriodCalculatorInterface
            {
                public static function getPriority(): int
                {
                    return 1;
                }

                public function support(ConfigureExpressPartialShipmentPayload $payload): bool
                {
                }

                public function calculate(
                    ConfigureExpressPartialShipmentPayload $payload,
                    array $periods
                ): DeliveryDateAndPeriodCalculatorResult {
                    return new DeliveryDateAndPeriodCalculatorResult(
                        $payload->getPartialShipment()->getBaseDeliveryDate()->modify('1 day'),
                        $periods
                    );
                }
            }
        );

        $stage = new CalculateDeliveryDatesStage($holidayService, $shippingPeriodRepository, $configService, $factory);

        self::assertSame($payload, $stage($payload));
        self::assertEquals(
            $baseShipmentDateTime->modify('1 day')->format('Y-m-d'),
            $payload->getPartialShipment()->getBaseDeliveryDate()->format('Y-m-d')
        );
    }

    /**
     * @return array
     */
    private function getPeriodsMocks(): array
    {
        $p1 = Mockery::mock(ShippingPeriod::class);
        $p1->shouldReceive([
            'getId'    => 1,
            'getStart' => new \DateTime('09:00:00'),
            'getEnd'   => new \DateTime('13:00:00'),
        ])
           ->once()
           ->withNoArgs();

        $p2 = Mockery::mock(ShippingPeriod::class);
        $p2->shouldReceive([
            'getId'    => 1,
            'getStart' => new \DateTime('14:00:00'),
            'getEnd'   => new \DateTime('18:00:00'),
        ])
           ->once()
           ->withNoArgs();

        $p3 = Mockery::mock(ShippingPeriod::class);
        $p3->shouldReceive([
            'getId'    => 1,
            'getStart' => new \DateTime('18:00:00'),
            'getEnd'   => new \DateTime('22:00:00'),
        ])
           ->once()
           ->withNoArgs();

        return [$p1, $p2, $p3];
    }
}
