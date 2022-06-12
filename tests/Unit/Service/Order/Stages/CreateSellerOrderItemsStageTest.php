<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Seller;
use App\Entity\SellerOrderItem;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\Order\Stages\CreateSellerOrderItemsStage;
use App\Service\Pipeline\AbstractPipelinePayload;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class CreateSellerOrderItemsStageTest extends MockeryTestCase
{
    public function testPriority(): void
    {
        self::assertEquals(88, CreateSellerOrderItemsStage::getPriority());
    }

    public function testTag(): void
    {
        self::assertEquals('app.pipeline_stage.order_processing', CreateSellerOrderItemsStage::getTag());
    }

    public function testItCanCreateSellerOrderItems(): void
    {
        $seller = m::mock(Seller::class);

        $inventory = m::mock(Inventory::class);
        $inventory->shouldReceive(['getSeller' => $seller, 'getLeadTime' => 1])
                  ->atMost(2)
                  ->withNoArgs();

        $orderItem = m::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);
        $orderItem->shouldReceive('setSellerOrderItem')
                  ->once()
                  ->with(\Mockery::type(SellerOrderItem::class))
                  ->andReturn();

        $order = m::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $entityManager = m::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->once()->with(m::type(SellerOrderItem::class))->andReturn();

        $payload = m::mock(AbstractPipelinePayload::class);
        $payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);
        $payload->shouldReceive('getEntityManager')->once()->withNoArgs()->andReturn($entityManager);

        $holidayService = \Mockery::mock(HolidayServiceInterface::class);
        $holidayService->shouldReceive('isOpenForSupply')
                       ->once()
                       ->with(\Mockery::type(\DateTimeImmutable::class))
                       ->andReturnTrue();
        $holidayService->shouldReceive('isOpenForSupply')
                       ->once()
                       ->with(\Mockery::type(\DateTimeImmutable::class), $seller)
                       ->andReturnTrue();

        $stage = new CreateSellerOrderItemsStage($holidayService);

        self::assertSame($payload, $stage($payload));
    }
}
