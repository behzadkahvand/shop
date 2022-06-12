<?php

namespace App\Tests\Unit\Service\Carrier\COD\Condition;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;
use App\Service\Carrier\COD\Condition\ShipmentStatusIsSentCondition;
use App\Service\Carrier\Exceptions\ShipmentStatusIsNotSentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class ShipmentStatusIsSentConditionTest extends MockeryTestCase
{
    public function testItThrowAnExceptionWhenShipmentStatusIsNotSent(): void
    {
        $this->expectException(ShipmentStatusIsNotSentException::class);

        $orderShipment = m::mock(OrderShipment::class);
        $orderShipment->shouldReceive('getStatus')
            ->once()
            ->withNoArgs()
            ->andReturn(OrderShipmentStatus::AFTER_SALES);

        (new ShipmentStatusIsSentCondition())->apply($orderShipment);
    }
}
