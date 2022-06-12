<?php

namespace App\Tests\Unit\Service\PartialShipment\Factory\Calculators\Express;

use App\Service\PartialShipment\Factory\Calculators\Express\ExpressShipmentDeliveryDateAndPeriodCalculatorFactory;
use App\Service\PartialShipment\Factory\Calculators\Express\ExpressShipmentDeliveryDateAndPeriodCalculatorInterface;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigureExpressPartialShipmentPayload;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ExpressShipmentDeliveryDateAndPeriodCalculatorFactoryTest
 */
final class ExpressShipmentDeliveryDateAndPeriodCalculatorFactoryTest extends MockeryTestCase
{
    public function testItReturnCalculatorThatSupportPayload(): void
    {
        $payload = new ConfigureExpressPartialShipmentPayload(
            \Mockery::mock(AbstractPartialShipment::class),
            new \DateTimeImmutable()
        );

        $calculator1 = \Mockery::mock(ExpressShipmentDeliveryDateAndPeriodCalculatorInterface::class);
        $calculator1->shouldReceive('support')->once()->with($payload)->andReturnFalse();

        $calculator2 = \Mockery::mock(ExpressShipmentDeliveryDateAndPeriodCalculatorInterface::class);
        $calculator2->shouldReceive('support')->once()->with($payload)->andReturnTrue();

        $calculators = [$calculator1, $calculator2];
        $factory     = new ExpressShipmentDeliveryDateAndPeriodCalculatorFactory($calculators);

        self::assertSame($calculator2, $factory->create($payload));
    }

    public function testItThrowExceptionIfNoneOfCalculatorsSupportPayload(): void
    {
        $payload = new ConfigureExpressPartialShipmentPayload(
            \Mockery::mock(AbstractPartialShipment::class),
            new \DateTimeImmutable()
        );

        $calculator1 = \Mockery::mock(ExpressShipmentDeliveryDateAndPeriodCalculatorInterface::class);
        $calculator1->shouldReceive('support')->once()->with($payload)->andReturnFalse();

        $calculator2 = \Mockery::mock(ExpressShipmentDeliveryDateAndPeriodCalculatorInterface::class);
        $calculator2->shouldReceive('support')->once()->with($payload)->andReturnFalse();

        $factory = new ExpressShipmentDeliveryDateAndPeriodCalculatorFactory([$calculator1, $calculator2]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'Unable to create an instance of %s',
            ExpressShipmentDeliveryDateAndPeriodCalculatorInterface::class
        ));

        $factory->create($payload);
    }
}
