<?php

namespace App\Tests\Unit\Service\Order\Listeners;

use App\Entity\Order;
use App\Events\Order\OrderRegisteredEvent;
use App\Service\Inventory\DepotInventory\DepotStatusInventoryService;
use App\Service\Order\Listeners\SendSmsNotificationToSellerIsDepotListener;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

final class SendSmsNotificationToSellerIsDepotListenerTest extends MockeryTestCase
{
    /**
     * @var DepotStatusInventoryService|m\LegacyMockInterface|m\MockInterface
     */
    private $depotStatusInventoryService;

    private SendSmsNotificationToSellerIsDepotListener $depotListener;

    /**
     * @var OrderRegisteredEvent|m\LegacyMockInterface|m\MockInterface
     */
    private $orderRegisteredEvent;

    public function testItCanGetSubscribedEvents(): void
    {
        $result = $this->depotListener::getSubscribedEvents();

        self::assertEquals([
            OrderRegisteredEvent::class => 'onOrderRegistered',
        ], $result);
    }

    public function testOnOrderRegistered()
    {
        $order = m::mock(Order::class);

        $order->shouldReceive('getId')->andReturn(10);

        $this->orderRegisteredEvent
            ->shouldReceive('getOrder')
            ->andReturn($order);

        $this->depotStatusInventoryService
            ->shouldReceive('handle')
            ->with(10)
            ->andReturn();

        $this->depotListener->onOrderRegistered($this->orderRegisteredEvent);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRegisteredEvent        = m::mock(OrderRegisteredEvent::class);
        $this->depotStatusInventoryService = m::mock(DepotStatusInventoryService::class);
        $this->depotListener               = new SendSmsNotificationToSellerIsDepotListener($this->depotStatusInventoryService);
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
