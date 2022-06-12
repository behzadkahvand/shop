<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Entity\Order;
use App\Service\Order\Condition\OrderConditionInterface;
use App\Service\Order\Stages\CheckOrderConditionsStage;
use App\Service\Pipeline\AbstractPipelinePayload;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class CheckOrderConditionsStageTest extends MockeryTestCase
{
    public function testItCanApplyOrderConditions(): void
    {
        $order = new Order();

        $payload = m::mock(AbstractPipelinePayload::class);
        $payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);

        $orderCondition = m::mock(OrderConditionInterface::class);
        $orderCondition->shouldReceive('apply')->once()->with($order)->andReturn();

        $stage = new CheckOrderConditionsStage($orderCondition);

        self::assertSame($payload, $stage($payload));
    }
}
