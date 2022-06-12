<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Order\Stages\DecreaseInventoryCountStockCountStage;
use App\Service\Pipeline\AbstractPipelinePayload;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

final class DecreaseInventoryCountStockCountStageTest extends MockeryTestCase
{
    public function testItCanDecreaseInventoryStockCount(): void
    {
        $orderItemQuantity = 3;

        $inventory = m::mock(Inventory::class);
        $inventory->shouldReceive('decreaseStockCount')->times($orderItemQuantity)->withNoArgs()->andReturnSelf();

        $orderItem = m::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);
        $orderItem->shouldReceive('getQuantity')->once()->withNoArgs()->andReturn($orderItemQuantity);

        $order = m::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $payload = m::mock(AbstractPipelinePayload::class);
        $payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);

        $stage = new DecreaseInventoryCountStockCountStage();

        self::assertSame($payload, $stage($payload));
    }
}
