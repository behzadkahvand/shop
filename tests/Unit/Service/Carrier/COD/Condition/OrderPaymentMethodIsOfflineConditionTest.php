<?php

namespace App\Tests\Unit\Service\Carrier\COD\Condition;

use App\Dictionary\OrderPaymentMethod;
use App\Entity\OrderShipment;
use App\Service\Carrier\COD\Condition\OrderPaymentMethodIsOfflineCondition;
use App\Service\Carrier\Exceptions\OrderPaymentMethodIsNotOfflineException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class OrderPaymentMethodIsOfflineConditionTest extends MockeryTestCase
{
    public function testItThrowAnExceptionWhenOrderPaymentMethodIsNotOffline(): void
    {
        $this->expectException(OrderPaymentMethodIsNotOfflineException::class);

        $orderShipment = m::mock(OrderShipment::class);
        $orderShipment->shouldReceive('getOrder->getPaymentMethod')
            ->once()
            ->withNoArgs()
            ->andReturn(OrderPaymentMethod::ONLINE);

        (new OrderPaymentMethodIsOfflineCondition())->apply($orderShipment);
    }
}
