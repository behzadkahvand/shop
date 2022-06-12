<?php

namespace App\Tests\Unit\Service\Order\Condition;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Condition\Exceptions\ProductIsNotActiveException;
use App\Service\Condition\ProductAvailabilityCondition as BaseProductAvailabilityCondition;
use App\Service\Order\Condition\ProductAvailabilityCondition;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ProductAvailabilityConditionTest extends MockeryTestCase
{
    public function testItThrowAnExceptionWhenProductIsNotActive(): void
    {
        $inventory = m::mock(Inventory::class);
        $inventory->shouldReceive('getProductIsActive')->once()->withNoArgs()->andReturn(false);

        $orderItem = m::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);

        $order = m::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $this->expectException(ProductIsNotActiveException::class);

        (new ProductAvailabilityCondition(new BaseProductAvailabilityCondition()))->apply($order);
    }

    public function testItThrowAnExceptionWhenProductIsNotConfirmed(): void
    {
        $inventory = m::mock(Inventory::class);
        $inventory->shouldReceive('getProductIsActive')->once()->withNoArgs()->andReturn(true);
        $inventory->shouldReceive('getProductStatus')->once()->withNoArgs()->andReturn(ProductStatusDictionary::WAITING_FOR_ACCEPT);

        $orderItem = m::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);

        $order = m::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $this->expectException(ProductIsNotActiveException::class);

        (new ProductAvailabilityCondition(new BaseProductAvailabilityCondition()))->apply($order);
    }
}
