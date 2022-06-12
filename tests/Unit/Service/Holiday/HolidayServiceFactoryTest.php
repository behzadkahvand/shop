<?php

namespace App\Tests\Unit\Service\Holiday;

use App\Service\Holiday\Adapters\FridayHolidayServiceAdapter;
use App\Service\Holiday\HolidayServiceFactory;
use App\Service\Holiday\HolidayServiceInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * Class HolidayServiceFactoryTest
 */
final class HolidayServiceFactoryTest extends MockeryTestCase
{
    public function testItThrowExceptionIfDriverIsNotAvailable()
    {
        $driver           = 'unavailable_driver';
        $availableDrivers = ['foo', 'bar'];
        $container        = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->once()->with($driver)->andReturn(false);

        $factory         = new HolidayServiceFactory($container, $availableDrivers);
        $expectedMessage = 'Invalid holiday service driver.';
        $expectedMessage .= sprintf(
            ' Received "%s", expected one of "%s"',
            $driver,
            implode('", "', $availableDrivers)
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $factory->create($driver);
    }

    public function testItCreateHolidayService()
    {
        $driver           = 'driver';
        $availableDrivers = ['driver'];
        $container        = \Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('has')->once()->with($driver)->andReturn(true);

        $container->shouldReceive('get')
                  ->once()
                  ->with($driver)
                  ->andReturn(\Mockery::mock(HolidayServiceInterface::class));

        $factory = new HolidayServiceFactory($container, $availableDrivers);

        $service = $factory->create($driver);

        $this->assertInstanceOf(HolidayServiceInterface::class, $service);
        $this->assertInstanceOf(FridayHolidayServiceAdapter::class, $service);
    }
}
