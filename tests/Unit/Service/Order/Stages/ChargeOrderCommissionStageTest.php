<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Entity\Order;
use App\Service\Commission\OrderCommissionChargerService;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\Stages\ChargeOrderCommissionStage;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

class ChargeOrderCommissionStageTest extends BaseUnitTestCase
{
    public function testShouldWorkCorrectly(): void
    {
        $payload = Mockery::mock(CreateOrderPayload::class);
        $order = Mockery::mock(Order::class);
        $orderCommissionCharger = Mockery::mock(OrderCommissionChargerService::class);
        $payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);
        $orderCommissionCharger->shouldReceive('charge')->once()->with($order)->andReturnNull();

        $sut = new ChargeOrderCommissionStage($orderCommissionCharger);

        $sut->__invoke($payload);
    }
}
