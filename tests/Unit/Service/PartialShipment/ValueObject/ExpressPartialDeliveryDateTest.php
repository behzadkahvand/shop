<?php

namespace App\Tests\Unit\Service\PartialShipment\ValueObject;

use App\Entity\ShippingPeriod;
use App\Service\PartialShipment\ValueObject\ExpressPartialDeliveryDate;
use App\Service\PartialShipment\ValueObject\PartialShipmentPeriod;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ExpressPartialDeliveryDateTest
 */
final class ExpressPartialDeliveryDateTest extends MockeryTestCase
{
    public function testItValidatePeriodsCount(): void
    {
        $message = sprintf('Class %s expects at least one period.', ExpressPartialDeliveryDate::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new ExpressPartialDeliveryDate(new \DateTime(), []);
    }

    public function testItValidatePeriodsClass(): void
    {
        $periods = ['bool' => true, 'array' => [], 'Instance of stdClass' => new \stdClass(), 'int' => 10];

        foreach ($periods as $type => $period) {
            try {
                new ExpressPartialDeliveryDate(new \DateTime(), [$period]);
            } catch (\InvalidArgumentException $e) {
                self::assertEquals(sprintf(
                    'Class %s expects an array of %s for periods. %s given.',
                    ExpressPartialDeliveryDate::class,
                    PartialShipmentPeriod::class,
                    $type
                ), $e->getMessage());
            }
        }
    }

    public function testItGetDeliveryDateAndPeriods(): void
    {
        $shippingPeriod = \Mockery::mock(ShippingPeriod::class);
        $shippingPeriod->shouldReceive([
                           'getId' => random_int(1, 10),
                           'getStart' => new \DateTime(),
                           'getEnd' => new \DateTime(),
                       ])
                       ->once()
                       ->withNoArgs();
        $periods                    = [new PartialShipmentPeriod($shippingPeriod)];
        $expressPartialDeliveryDate = new ExpressPartialDeliveryDate(new \DateTime(), $periods);

        self::assertSame($periods, $expressPartialDeliveryDate->getPeriods());
    }

    public function testItReturnFalseInValidationIfGivenDateIsNotEqualToDeliveryDate(): void
    {
        $shippingPeriod = \Mockery::mock(ShippingPeriod::class);
        $shippingPeriod->shouldReceive([
                           'getId' => random_int(1, 10),
                           'getStart' => new \DateTime(),
                           'getEnd' => new \DateTime(),
                       ])
                       ->once()
                       ->withNoArgs();

        $periods                    = [new PartialShipmentPeriod($shippingPeriod)];
        $expressPartialDeliveryDate = new ExpressPartialDeliveryDate(new \DateTime(), $periods);

        self::assertFalse($expressPartialDeliveryDate->validate(new \DateTime(' 3 day'), new \stdClass()));
    }

    public function testItReturnFalseInValidationIfGivenTimeIsNotInPeriods(): void
    {
        $shippingPeriod = \Mockery::mock(ShippingPeriod::class);
        $shippingPeriod->shouldReceive([
                           'getId' => random_int(1, 10),
                           'getStart' => new \DateTime('09:00'),
                           'getEnd' => new \DateTime('14:00'),
                       ])
                       ->once()
                       ->withNoArgs();

        $periods                    = [new PartialShipmentPeriod($shippingPeriod)];
        $expressPartialDeliveryDate = new ExpressPartialDeliveryDate(new \DateTime(), $periods);

        self::assertFalse($expressPartialDeliveryDate->validate(new \DateTime('15:00'), $this->getContext()));
    }

    public function testItReturnFalseInValidationIfGivenTimeIsNotSelectable(): void
    {
        $shippingPeriod = \Mockery::mock(ShippingPeriod::class);
        $shippingPeriod->shouldReceive([
                           'getId' => random_int(1, 10),
                           'getStart' => new \DateTime('09:00'),
                           'getEnd' => new \DateTime('14:00'),
                       ])
                       ->once()
                       ->withNoArgs();

        $periods                    = [new PartialShipmentPeriod($shippingPeriod, false)];
        $expressPartialDeliveryDate = new ExpressPartialDeliveryDate(new \DateTime(), $periods);

        self::assertFalse($expressPartialDeliveryDate->validate(new \DateTime('09:00'), $this->getContext()));
    }

    public function testItReturnTrueIfGivenTimeIsValid(): void
    {
        $shippingPeriod = \Mockery::mock(ShippingPeriod::class);
        $shippingPeriod->shouldReceive([
                           'getId' => random_int(1, 10),
                           'getStart' => new \DateTime('09:00'),
                           'getEnd' => new \DateTime('14:00'),
                       ])
                       ->once()
                       ->withNoArgs();

        $periods                    = [new PartialShipmentPeriod($shippingPeriod, true)];
        $expressPartialDeliveryDate = new ExpressPartialDeliveryDate(new \DateTime(), $periods);

        self::assertTrue($expressPartialDeliveryDate->validate(new \DateTime('09:00'), new \stdClass()));
    }

    private function getContext(): object
    {
        $buildViolationValidator = function ($message) {
            $this->assertEquals('Selected time is not valid.', $message);
        };
        $atPathValidator = function ($path) {
            $this->assertEquals('deliveryDate', $path);
        };

        return new class ($buildViolationValidator, $atPathValidator) {
            private $buildViolationValidator;
            private $atPathValidator;
            public function __construct($buildViolationValidator, $atPathValidator)
            {
                $this->buildViolationValidator = $buildViolationValidator;
                $this->atPathValidator = $atPathValidator;
            }
            public function buildViolation($message)
            {
                ($this->buildViolationValidator)($message);

                return $this;
            }
            public function atPath($path)
            {
                ($this->atPathValidator)($path);

                return $this;
            }
            public function addViolation()
            {
            }
        };
    }
}
