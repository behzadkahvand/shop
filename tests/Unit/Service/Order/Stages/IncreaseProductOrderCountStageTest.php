<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Service\Order\Stages\IncreaseProductOrderCountStage;
use App\Service\Pipeline\AbstractPipelinePayload;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

final class IncreaseProductOrderCountStageTest extends MockeryTestCase
{
    public function testItCanIncreaseProductOrderCount(): void
    {
        $product = m::mock(Product::class);
        $product->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $product->shouldReceive('incrementOrderCount')->once()->withNoArgs()->andReturnSelf();

        $variant = m::mock(ProductVariant::class);
        $variant->shouldReceive('getProduct')->once()->withNoArgs()->andReturn($product);

        $inventory = m::mock(Inventory::class);
        $inventory->shouldReceive('getVariant')->once()->withNoArgs()->andReturn($variant);

        $orderItem = m::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);

        $order = m::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $payload = m::mock(AbstractPipelinePayload::class);
        $payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);

        $stage = new IncreaseProductOrderCountStage();

        self::assertSame($payload, $stage($payload));
    }
}
