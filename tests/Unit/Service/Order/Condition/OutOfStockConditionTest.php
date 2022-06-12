<?php

namespace App\Tests\Unit\Service\Order\Condition;

use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Condition\Exceptions\OutOfStockException;
use App\Service\Condition\OutOfStockCondition as BaseOutOfStockCondition;
use App\Service\Order\Condition\OutOfStockCondition;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class OutOfStockConditionTest extends MockeryTestCase
{
    public function testItThrowAnExceptionWhenQuantityIsGreaterThanAvailableStock(): void
    {
        $inventory = m::mock(Inventory::class);
        $inventory->shouldReceive('getSellerStock')->once()->withNoArgs()->andReturn(10);

        $orderItem = m::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);
        $orderItem->shouldReceive('getQuantity')->once()->withNoArgs()->andReturn(11);

        $order = m::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $this->expectException(OutOfStockException::class);

        (new OutOfStockCondition(new BaseOutOfStockCondition()))->apply($order);
    }
}
