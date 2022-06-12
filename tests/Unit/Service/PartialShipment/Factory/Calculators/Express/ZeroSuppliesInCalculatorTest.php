<?php

namespace App\Tests\Unit\Service\PartialShipment\Factory\Calculators\Express;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\ShippingCategory;
use App\Entity\ShippingPeriod;
use App\Entity\Zone;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\Factory\Calculators\Express\ZeroSuppliesInCalculator;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigureExpressPartialShipmentPayload;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\Types\ExpressPartialShipment;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\PartialShipment\ValueObject\PartialShipmentPeriod;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ZeroSuppliesInCalculatorTest
 */
final class ZeroSuppliesInCalculatorTest extends MockeryTestCase
{
    public function testItSupportItemsWithZeroSuppliesIn(): void
    {
        $calculator = new ZeroSuppliesInCalculator(
            Mockery::mock(HolidayServiceInterface::class),
            Mockery::mock(ConfigurationServiceInterface::class)
        );

        $partialShipment = Mockery::mock(AbstractPartialShipment::class);
        $partialShipment->shouldReceive('suppliesInIsZero')->twice()->withNoArgs()->andReturn(true, false);

        $payload = new ConfigureExpressPartialShipmentPayload($partialShipment, new \DateTimeImmutable());

        self::assertTrue($calculator->support($payload));
        self::assertFalse($calculator->support($payload));
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $orderCreatedAtDate
     * @param int $orderCreatedAtTime
     * @param int $firstOpenPeriod
     * @param string $expectedDeliveryDate
     * @param string|null $holidayDate
     *
     * @throws \Exception
     */
    public function testItCalculateDeliveryDates(
        string $orderCreatedAtDate,
        int $orderCreatedAtTime,
        int $firstOpenPeriod,
        string $expectedDeliveryDate,
        ?string $holidayDate = null
    ): void {
        $item                 = Mockery::mock(PartialShipmentItem::class);
        $orderDateTime        = (new DateTimeImmutable($orderCreatedAtDate))->setTime($orderCreatedAtTime, 0, 0);
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
                       ->between(1, 2)
                       ->with(Mockery::type(DateTimeImmutable::class))
                       ->andReturnUsing(function (DateTimeImmutable $datetime) use ($holidayDate) {
                        if (!$holidayDate) {
                            return $datetime;
                        }

                        if ($holidayDate === $datetime->format('Y-m-d')) {
                            return $datetime->modify('1 day');
                        }

                           return $datetime;
                       });

        $periods = array_map([PartialShipmentPeriod::class, 'fromShippingPeriod'], $this->getPeriodsMocks());

        $configService = \Mockery::mock(ConfigurationServiceInterface::class);
        $configService->shouldReceive('findByCodes')
                      ->once()
                      ->with(
                          ConfigurationCodeDictionary::WAREHOUSE_START_TIME,
                          ConfigurationCodeDictionary::WAREHOUSE_PROCESSING_DURATION_IN_HOUR,
                          ConfigurationCodeDictionary::WAREHOUSE_END_TIME,
                      )
                      ->andReturn([
                          ConfigurationCodeDictionary::WAREHOUSE_START_TIME => null,
                          ConfigurationCodeDictionary::WAREHOUSE_PROCESSING_DURATION_IN_HOUR => null,
                          ConfigurationCodeDictionary::WAREHOUSE_END_TIME => null,
                      ]);

        $stage = new ZeroSuppliesInCalculator($holidayService, $configService);

        $result = $stage->calculate($payload, $periods);

        foreach ($result->getPeriods() as $i => $period) {
            self::assertInstanceOf(PartialShipmentPeriod::class, $period);

            if ($i < ($firstOpenPeriod - 1)) {
                self::assertFalse($period->isSelectable());
            } else {
                self::assertTrue($period->isSelectable());
            }
        }

        self::assertEquals($expectedDeliveryDate, $result->getBaseDeliveryDate()->format('Y-m-d'));
    }

    public function dataProvider(): iterable
    {
        yield array_values([
            'orderCreatedAtDate'   => '2020-04-01',
            'orderCreatedAtTime'   => 10,
            'firstOpenPeriod'      => 3,
            'expectedDeliveryDate' => '2020-04-02',
            'holidayDate'          => '2020-04-01',
        ]);
        yield array_values([
            'orderCreatedAtDate'   => '2020-04-01',
            'orderCreatedAtTime'   => 13,
            'firstOpenPeriod'      => 1,
            'expectedDeliveryDate' => '2020-04-02',
        ]);
        yield array_values([
            'orderCreatedAtDate'   => '2020-04-01',
            'orderCreatedAtTime'   => 17,
            'firstOpenPeriod'      => 1,
            'expectedDeliveryDate' => '2020-04-02',
            'holidayDate'          => '2020-04-01',
        ]);
        yield array_values([
            'orderCreatedAtDate'   => '2020-04-01',
            'orderCreatedAtTime'   => 22,
            'firstOpenPeriod'      => 3,
            'expectedDeliveryDate' => '2020-04-02',
        ]);
        yield array_values([
            'orderCreatedAtDate'   => '2020-04-01',
            'orderCreatedAtTime'   => 3,
            'firstOpenPeriod'      => 3,
            'expectedDeliveryDate' => '2020-04-01',
        ]);
        yield array_values([
            'orderCreatedAtDate'   => '2020-04-01',
            'orderCreatedAtTime'   => 17,
            'firstOpenPeriod'      => 1,
            'expectedDeliveryDate' => '2020-04-03',
            'holidayDate'          => '2020-04-02',
        ]);
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
