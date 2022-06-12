<?php

namespace App\Tests\Unit\Service\Order\Condition;

use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Condition\Exceptions\InventoryIsNotActiveException;
use App\Service\Condition\InventoryIsActiveCondition as BaseInventoryIsActiveCondition;
use App\Service\Order\Condition\InventoryIsActiveCondition;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class InventoryIsNotActiveConditionTest extends MockeryTestCase
{
    public function testItThrowAnExceptionWhenInventoryIsNotActive(): void
    {
        $inventory = m::mock(Inventory::class);
        $inventory->shouldReceive('getIsActive')->once()->withNoArgs()->andReturn(false);

        $orderItem = m::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);

        $order = m::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $this->expectException(InventoryIsNotActiveException::class);

        (new InventoryIsActiveCondition(new BaseInventoryIsActiveCondition()))->apply($order);
    }
}
