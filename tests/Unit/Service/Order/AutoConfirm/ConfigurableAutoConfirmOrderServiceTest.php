<?php

namespace App\Tests\Unit\Service\Order\AutoConfirm;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Configuration;
use App\Entity\Order;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Order\AutoConfirm\AutoConfirmOrderServiceInterface;
use App\Service\Order\AutoConfirm\ConfigurableAutoConfirmOrderService;
use App\Service\Order\OrderIsNotConfirmableException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ConfigurableAutoConfirmOrderServiceTest
 */
final class ConfigurableAutoConfirmOrderServiceTest extends MockeryTestCase
{
    public function testItCheckOrderIsConfirmableWhenConfigIsNotSet(): void
    {
        $order = Mockery::mock(Order::class);

        $configService = Mockery::mock(ConfigurationServiceInterface::class);
        $configService->shouldReceive(['findByCode' => null])
                      ->twice()
                      ->with(ConfigurationCodeDictionary::AUTO_CONFIRM_ORDER);

        $decorated = Mockery::mock(AutoConfirmOrderServiceInterface::class);
        $decorated->shouldReceive(['isConfirmable' => false])->once()->with($order);

        $service = new ConfigurableAutoConfirmOrderService($decorated, $configService);

        self::assertFalse($service->isConfirmable($order));

        $decorated = Mockery::mock(AutoConfirmOrderServiceInterface::class);
        $decorated->shouldReceive(['isConfirmable' => true])->once()->with($order);

        $service = new ConfigurableAutoConfirmOrderService($decorated, $configService);

        self::assertTrue($service->isConfirmable($order));
    }

    public function testItCheckOrderIsConfirmableWhenConfigIsSet(): void
    {
        $order = Mockery::mock(Order::class);

        $config = Mockery::mock(Configuration::class);
        $config->shouldReceive(['getValue' => false])->once()->withNoArgs();

        $configService = Mockery::mock(ConfigurationServiceInterface::class);
        $configService->shouldReceive(['findByCode' => $config])
                      ->twice()
                      ->with(ConfigurationCodeDictionary::AUTO_CONFIRM_ORDER);

        $decorated = Mockery::mock(AutoConfirmOrderServiceInterface::class);

        $service = new ConfigurableAutoConfirmOrderService($decorated, $configService);

        self::assertFalse($service->isConfirmable($order));

        $config->shouldReceive(['getValue' => true])->twice()->withNoArgs();

        $decorated = Mockery::mock(AutoConfirmOrderServiceInterface::class);
        $decorated->shouldReceive('isConfirmable')->twice()->with($order)->andReturn(true, false);

        $service = new ConfigurableAutoConfirmOrderService($decorated, $configService);

        self::assertTrue($service->isConfirmable($order));
        self::assertFalse($service->isConfirmable($order));
    }

    public function testItThrowExceptionIOrderIsNotConfirmableButConfirmMethodIsCalled(): void
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive(['getIdentifier' => 1])->once()->withNoArgs();

        $configService = Mockery::mock(ConfigurationServiceInterface::class);
        $configService->shouldReceive(['findByCode' => null])
                      ->once()
                      ->with(ConfigurationCodeDictionary::AUTO_CONFIRM_ORDER);

        $decorated = Mockery::mock(AutoConfirmOrderServiceInterface::class);
        $decorated->shouldReceive(['isConfirmable' => false])->once()->with($order);

        $service = new ConfigurableAutoConfirmOrderService($decorated, $configService);

        $this->expectException(OrderIsNotConfirmableException::class);
        $this->expectExceptionMessage('Order with identifier 1 is not confirmable.');

        $service->confirm($order);
    }

    public function testItConfirmAConfirmableOrder(): void
    {
        $order = Mockery::mock(Order::class);

        $configService = Mockery::mock(ConfigurationServiceInterface::class);
        $configService->shouldReceive(['findByCode' => null])
                      ->once()
                      ->with(ConfigurationCodeDictionary::AUTO_CONFIRM_ORDER);

        $decorated = Mockery::mock(AutoConfirmOrderServiceInterface::class);
        $decorated->shouldReceive('isConfirmable')->once()->with($order)->andReturnTrue();
        $decorated->shouldReceive('confirm')->once()->with($order)->andReturn();

        $service = new ConfigurableAutoConfirmOrderService($decorated, $configService);

        $service->confirm($order);
    }
}
