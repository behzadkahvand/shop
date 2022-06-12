<?php

namespace App\Tests\Unit\Service\Order\Condition;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Order\Condition\Exceptions\OrderItemInventoryPriceHasBeenUpdatedException;
use App\Service\Order\Condition\PriceIntegrityCondition;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PriceIntegrityConditionTest extends MockeryTestCase
{
    public function testItThrowAnExceptionWhenOrderItemPriceHasUpdated(): void
    {
        $orderItem = m::mock(OrderItem::class);
        $orderItem->shouldReceive('priceHasBeenUpdated')->once()->withNoArgs()->andReturn(true);

        $order = m::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $this->expectException(OrderItemInventoryPriceHasBeenUpdatedException::class);

        (new PriceIntegrityCondition())->apply($order);
    }
}
