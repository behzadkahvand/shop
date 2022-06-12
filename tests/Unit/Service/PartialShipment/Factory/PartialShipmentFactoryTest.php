<?php

namespace App\Tests\Unit\Service\PartialShipment\Factory;

use App\Entity\ShippingCategory;
use App\Entity\Zone;
use App\Service\PartialShipment\Exceptions\MinimumShipmentItemCountException;
use App\Service\PartialShipment\Factory\PartialShipmentFactory;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigureExpressPartialShipmentPayload;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigurePartialShipmentPayload;
use App\Service\PartialShipment\Factory\Pipeline\Payload\CreatePartialShipmentPayload;
use App\Service\PartialShipment\Types\ExpressPartialShipment;
use App\Service\PartialShipment\Types\PartialShipment;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\Pipeline\PipelineInterface;
use App\Service\Pipeline\PipelineRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class PartialShipmentFactoryTest
 */
final class PartialShipmentFactoryTest extends MockeryTestCase
{
    public function testItThrowExceptionIfNoShipmentItemIsProvided()
    {
        $factory = new PartialShipmentFactory(\Mockery::mock(PipelineRepository::class));

        self::expectException(MinimumShipmentItemCountException::class);
        self::expectExceptionMessage('At least 1 shipment item is needed to create a partial shipment');

        $factory->create(new \DateTimeImmutable(), \Mockery::mock(Zone::class), [], true);
    }

    public function testItThrowExceptionIfShipmentItemsHasNoShippingCategory()
    {
        $factory = new PartialShipmentFactory(\Mockery::mock(PipelineRepository::class));

        self::expectException(MinimumShipmentItemCountException::class);
        self::expectExceptionMessage(
            'Unable to create partial shipment as none of the shipping items has a shipping category.'
        );

        $item = \Mockery::mock(PartialShipmentItem::class);
        $item->shouldReceive('getShippingCategory')->once()->withNoArgs()->andReturnNull();

        $factory->create(new \DateTimeImmutable(), \Mockery::mock(Zone::class), [$item], true);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $configurator
     * @param bool $isExpress
     * @param string $expectedType
     */
    public function testItCreatePartialShipment(string $configurator, bool $isExpress, string $expectedType)
    {
        $pipelineResult = null;

        $configurePartialShipmentPipeline = \Mockery::mock(PipelineInterface::class);

        $createPartialShipmentPipeline = \Mockery::mock(PipelineInterface::class);
        $createPartialShipmentPipeline->shouldReceive('pipe')
                                      ->once()
                                      ->with(\Mockery::type(\Closure::class))
                                      ->andReturnSelf();
        $createPartialShipmentPipeline->shouldReceive('pipe')
                                      ->once()
                                      ->with($configurePartialShipmentPipeline)
                                      ->andReturnSelf();
        $createPartialShipmentPipeline->shouldReceive('process')
                                      ->once()
                                      ->with(\Mockery::type(CreatePartialShipmentPayload::class))
                                      ->andReturnUsing(fn($payload) => $payload);

        $pipelineRepository = \Mockery::mock(PipelineRepository::class);
        $pipelineRepository->shouldReceive('getByPayload')
                           ->once()
                           ->with(CreatePartialShipmentPayload::class)
                           ->andReturn($createPartialShipmentPipeline);
        $pipelineRepository->shouldReceive('getByPayload')
                           ->once()
                           ->with($configurator)
                           ->andReturn($configurePartialShipmentPipeline);

        $factory = new PartialShipmentFactory($pipelineRepository);

        $item = \Mockery::mock(PartialShipmentItem::class);
        $item->shouldReceive('getShippingCategory')
             ->once()
             ->withNoArgs()
             ->andReturn(\Mockery::mock(ShippingCategory::class));

        $partialShipment = $factory->create(new \DateTimeImmutable(), \Mockery::mock(Zone::class), [$item], $isExpress);

        self::assertInstanceOf($expectedType, $partialShipment);
    }

    public function dataProvider()
    {
        return [
            [ConfigurePartialShipmentPayload::class, false, PartialShipment::class],
            [ConfigureExpressPartialShipmentPayload::class, true, ExpressPartialShipment::class],
        ];
    }
}
