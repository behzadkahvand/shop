<?php

namespace App\Tests\Unit\Service\Order\Condition;

use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Condition\Exceptions\MaxPurchasePerOrderExceededException;
use App\Service\Order\Condition\MaxPurchasePerOrderCondition;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use App\Service\Condition\MaxPurchasePerOrderCondition as BaseMaxPurchasePerOrderCondition;
use Mockery as m;

class MaxPurchasePerOrderConditionTest extends MockeryTestCase
{
    public function testItThrowAnExceptionWhenQuantityIsNegative(): void
    {
        $inventory = m::mock(Inventory::class);

        $orderItem = m::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);
        $orderItem->shouldReceive('getQuantity')->once()->withNoArgs()->andReturn(-1);

        $order = m::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $this->expectException(MaxPurchasePerOrderExceededException::class);

        (new MaxPurchasePerOrderCondition(new BaseMaxPurchasePerOrderCondition()))->apply($order);
    }

    public function testItThrowAnExceptionWhenQuantityIsGreaterThanMaxPurchasePerOrder(): void
    {
        $inventory = m::mock(Inventory::class);
        $inventory->shouldReceive('getMaxPurchasePerOrder')->once()->withNoArgs()->andReturn(10);

        $orderItem = m::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);
        $orderItem->shouldReceive('getQuantity')->once()->withNoArgs()->andReturn(11);

        $order = m::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $this->expectException(MaxPurchasePerOrderExceededException::class);

        (new MaxPurchasePerOrderCondition(new BaseMaxPurchasePerOrderCondition()))->apply($order);
    }
}
