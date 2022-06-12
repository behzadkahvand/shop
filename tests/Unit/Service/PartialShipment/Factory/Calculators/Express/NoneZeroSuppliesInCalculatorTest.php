<?php

namespace App\Tests\Unit\Service\PartialShipment\Factory\Calculators\Express;

use App\Entity\ShippingPeriod;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\Factory\Calculators\Express\NoneZeroSuppliesInCalculator;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigureExpressPartialShipmentPayload;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\ValueObject\PartialShipmentPeriod;
use DateTimeInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class NoneZeroSuppliesInCalculatorTest
 */
final class NoneZeroSuppliesInCalculatorTest extends MockeryTestCase
{
    public function testItSupportItemsWithZeroSuppliesIn(): void
    {
        $holidayService = Mockery::mock(HolidayServiceInterface::class);
        $calculator = new NoneZeroSuppliesInCalculator($holidayService);

        $partialShipment = Mockery::mock(AbstractPartialShipment::class);
        $partialShipment->shouldReceive('suppliesInIsZero')->twice()->withNoArgs()->andReturn(true, false);

        self::assertFalse(
            $calculator->support(new ConfigureExpressPartialShipmentPayload(
                $partialShipment,
                new \DateTimeImmutable()
            ))
        );

        self::assertTrue(
            $calculator->support(new ConfigureExpressPartialShipmentPayload(
                $partialShipment,
                new \DateTimeImmutable()
            ))
        );
    }

    public function testItCalculateBaseDeliveryDatesAndPeriods(): void
    {
        $baseDeliveryDate = new \DateTimeImmutable();

        $partialShipment  = Mockery::mock(AbstractPartialShipment::class);
        $partialShipment->shouldReceive('getBaseDeliveryDate')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($baseDeliveryDate);

        $payload = new ConfigureExpressPartialShipmentPayload(
            $partialShipment,
            new \DateTimeImmutable('14:00:00')
        );

        $shippingPeriod = Mockery::mock(ShippingPeriod::class);
        $shippingPeriod->shouldReceive('getId')->twice()->withNoArgs()->andReturn(1, 2);
        $shippingPeriod->shouldReceive('getStart')
                       ->twice()
                       ->withNoArgs()
                       ->andReturn(new \DateTime('09:00'), new \DateTime('14:00'));
        $shippingPeriod->shouldReceive('getEnd')
                       ->twice()
                       ->withNoArgs()
                       ->andReturn(new \DateTime('13:00'), new \DateTime('18:00'));

        $period1 = new PartialShipmentPeriod($shippingPeriod);
        $period2 = new PartialShipmentPeriod($shippingPeriod);
        $periods = [$period1, $period2];

        $holidayService = Mockery::mock(HolidayServiceInterface::class);
        $calculator = new NoneZeroSuppliesInCalculator($holidayService);

        $holidayService
            ->expects('getFirstOpenShipmentDateSince')
            ->with(Mockery::type(DateTimeInterface::class))
            ->andReturn(\DateTime::createFromImmutable($baseDeliveryDate->modify('1 day')));

        $result = $calculator->calculate($payload, $periods);

        self::assertEquals(
            $baseDeliveryDate->modify('1 day')->format('Y-m-d H:i'),
            $result->getBaseDeliveryDate()->format('Y-m-d H:i')
        );

        self::assertSame($periods, $result->getPeriods());

        foreach ($result->getPeriods() as $p) {
            self::assertTrue($p->isSelectable());
        }
    }
}
