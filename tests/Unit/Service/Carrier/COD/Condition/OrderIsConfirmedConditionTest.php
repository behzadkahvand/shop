<?php

namespace App\Tests\Unit\Service\Carrier\COD\Condition;

use App\Dictionary\OrderStatus;
use App\Entity\OrderShipment;
use App\Service\Carrier\COD\Condition\OrderIsConfirmedCondition;
use App\Service\Carrier\Exceptions\OrderIsNotConfirmedException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class OrderIsConfirmedConditionTest extends MockeryTestCase
{
    public function testItThrowAnExceptionWhenOrderIsNotConfirmed(): void
    {
        $this->expectException(OrderIsNotConfirmedException::class);

        $orderShipment = m::mock(OrderShipment::class);
        $orderShipment->shouldReceive('getOrder->getStatus')
            ->once()
            ->withNoArgs()
            ->andReturn(OrderStatus::REFUND);

        (new OrderIsConfirmedCondition())->apply($orderShipment);
    }
}
