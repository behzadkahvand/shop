<?php

namespace App\Tests\Unit\Service\Order\Listeners;

use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Events\Order\OrderRegisteredEvent;
use App\Service\Notification\DTOs\Seller\OnDemandInventoryIsOutOfStock;
use App\Service\Notification\NotificationService;
use App\Service\Order\Listeners\OutOfStockInventoryNotificationListener;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

final class OutOfStockInventoryNotificationListenerTest extends BaseUnitTestCase
{
    private OutOfStockInventoryNotificationListener|null $sut;

    private NotificationService|LegacyMockInterface|MockInterface|null $notificationService;

    private OrderRegisteredEvent|LegacyMockInterface|MockInterface|null $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event               = m::mock(OrderRegisteredEvent::class);
        $this->notificationService = m::mock(NotificationService::class);

        $this->sut = new OutOfStockInventoryNotificationListener($this->notificationService);
    }

    public function testShouldSendNotificationForOutOfStockInventories(): void
    {
        $order     = m::mock(Order::class);
        $orderItem = m::mock(OrderItem::class);
        $inventory = m::mock(Inventory::class);

        $this->event->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);
        $inventory->shouldReceive('getLeadTime')->once()->withNoArgs()->andReturn(0);
        $inventory->shouldReceive('getSellerStock')->once()->withNoArgs()->andReturn(0);
        $this->notificationService->shouldReceive('send')->once()->with(OnDemandInventoryIsOutOfStock::class);

        $this->sut->onOrderRegistered($this->event);
    }

    public function testShouldNotSendNotificationIfInventoryIsNotTrackable(): void
    {
        $order     = m::mock(Order::class);
        $orderItem = m::mock(OrderItem::class);
        $inventory = m::mock(Inventory::class);

        $this->event->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);
        $inventory->shouldReceive('getLeadTime')->once()->withNoArgs()->andReturn(1);

        $this->sut->onOrderRegistered($this->event);
    }

    public function testShouldNotSendNotificationIfInventoryIsNotOnDemand(): void
    {
        $order     = m::mock(Order::class);
        $orderItem = m::mock(OrderItem::class);
        $inventory = m::mock(Inventory::class);

        $this->event->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);
        $inventory->shouldReceive('getLeadTime')->once()->withNoArgs()->andReturn(2);


        $this->sut->onOrderRegistered($this->event);
    }

    public function testShouldNotSendNotificationIfStockIsNotZero(): void
    {
        $order     = m::mock(Order::class);
        $orderItem = m::mock(OrderItem::class);
        $inventory = m::mock(Inventory::class);

        $this->event->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));
        $orderItem->shouldReceive('getInventory')->once()->withNoArgs()->andReturn($inventory);
        $inventory->shouldReceive('getLeadTime')->once()->withNoArgs()->andReturn(2);


        $this->sut->onOrderRegistered($this->event);
    }
}
