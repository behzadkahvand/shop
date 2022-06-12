<?php

namespace App\Tests\Unit\Service\Holiday\Adapters;

use App\Entity\Seller;
use App\Service\Holiday\Adapters\CallbackHolidayServiceAdapter;
use PHPUnit\Framework\TestCase;

/**
 * Class CallbackHolidayServiceTest
 */
final class CallbackHolidayServiceTest extends TestCase
{
    public function testItCheckDateIsOpenForShipment()
    {
        $service = new CallbackHolidayServiceAdapter('always_true_function');

        $this->assertTrue($service->isOpenForShipment(new \DateTime()));

        $service = new CallbackHolidayServiceAdapter('always_false_function');

        $this->assertFalse($service->isOpenForShipment(new \DateTime(), new Seller()));
    }

    public function testItCheckDateIsOpenForSupply()
    {
        $service = new CallbackHolidayServiceAdapter('always_true_function');

        $this->assertTrue($service->isOpenForShipment(new \DateTime()));

        $service = new CallbackHolidayServiceAdapter('always_false_function');

        $this->assertFalse($service->isOpenForShipment(new \DateTime(), new Seller()));
    }

    public function testItGetsFirstOpenShipmentDateSince()
    {
        $service = new CallbackHolidayServiceAdapter('always_true_function');

        $this->assertInstanceOf(\DateTimeInterface::class, $service->getFirstOpenShipmentDateSince(new \DateTime()));
    }

    public function testItGetsDriverName()
    {
        $this->assertEquals('callback', CallbackHolidayServiceAdapter::getName());
    }
}
