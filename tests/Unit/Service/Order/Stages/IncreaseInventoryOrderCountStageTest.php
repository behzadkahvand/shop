<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Order\Stages\IncreaseInventoryOrderCountStage;
use App\Service\Pipeline\AbstractPipelinePayload;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * Class IncreaseInventoryOrderCountStageTest
 */
final class IncreaseInventoryOrderCountStageTest extends TestCase
{
    public function testItCanIncreaseInventoryOrderCount()
    {
        $inventory = \Mockery::mock(Inventory::class);
        $inventory->shouldReceive('incrementOrderCount')->once()->withNoArgs()->andReturnSelf();

        $orderItem = \Mockery::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);

        $order = \Mockery::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $payload = \Mockery::mock(AbstractPipelinePayload::class);
        $payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);

        $stage = new IncreaseInventoryOrderCountStage();

        self::assertSame($payload, $stage($payload));
    }
}
