<?php

namespace App\Tests\Unit\Service\Order\Condition;

use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Condition\Exceptions\InventoryIsNotConfirmedException;
use App\Service\Condition\InventoryIsConfirmedCondition as BaseInventoryIsConfirmedCondition;
use App\Service\Order\Condition\InventoryIsConfirmedCondition;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class InventoryIsNotConfirmedConditionTest extends MockeryTestCase
{
    public function testItThrowAnExceptionWhenInventoryIsNotConfirmed(): void
    {
        $inventory = m::mock(Inventory::class);
        $inventory->shouldReceive('isConfirmed')->once()->withNoArgs()->andReturn(false);

        $orderItem = m::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);

        $order = m::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $this->expectException(InventoryIsNotConfirmedException::class);

        (new InventoryIsConfirmedCondition(new BaseInventoryIsConfirmedCondition()))->apply($order);
    }
}
