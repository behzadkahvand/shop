<?php

namespace App\Tests\Unit\Service\Carrier\COD\Condition;

use App\Entity\OrderShipment;
use App\Service\Carrier\COD\Condition\OrderIsNotFullyPaidCondition;
use App\Service\Carrier\Exceptions\OrderAlreadyHasBeenFullyPaidException;
use DateTime;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class OrderIsNotFullyPaidConditionTest extends MockeryTestCase
{
    public function testItThrowAnExceptionWhenOrderAlreadyHasBeenFullyPaid(): void
    {
        $this->expectException(OrderAlreadyHasBeenFullyPaidException::class);

        $orderShipment = m::mock(OrderShipment::class);
        $orderShipment->shouldReceive('getOrder->getPaidAt')
            ->once()
            ->withNoArgs()
            ->andReturn(new DateTime());

        (new OrderIsNotFullyPaidCondition())->apply($orderShipment);
    }
}
