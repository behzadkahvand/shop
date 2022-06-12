<?php

namespace App\Tests\Unit\Service\Log;

use App\Entity\Admin;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderItemDeletedLog;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use App\Service\Log\OrderLogService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OrderLogServiceTest extends MockeryTestCase
{
    protected ?EntityManagerInterface $entityManger;

    protected ?OrderLogService $orderLogService;

    private ?int $orderId;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|OrderRepository|null $orderRepositoryMock;
    private OrderItemRepository|Mockery\LegacyMockInterface|Mockery\MockInterface|null $orderItemRepositoryMock;
    private Admin|Mockery\LegacyMockInterface|Mockery\MockInterface|null $adminMock;
    private Mockery\LegacyMockInterface|OrderItem|Mockery\MockInterface|null $orderItemMock;
    private Order|Mockery\LegacyMockInterface|Mockery\MockInterface|null $orderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepositoryMock = Mockery::mock(OrderRepository::class);
        $this->orderItemRepositoryMock = Mockery::mock(OrderItemRepository::class);
        $this->orderLogService = new OrderLogService($this->orderRepositoryMock, $this->orderItemRepositoryMock);

        $this->adminMock = Mockery::mock(Admin::class);
        $this->orderItemMock = Mockery::mock(OrderItem::class);
        $this->orderMock = Mockery::mock(Order::class);

        $this->orderId = 14;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->orderLogService);
        $this->entityManger = null;
    }

    public function testItCanCallCreateOrderItemDeletedLog()
    {
        $this->orderItemMock->expects('setDeletedBy')->andReturnSelf();

        $this->orderItemRepositoryMock->expects('find')
        ->andReturn($this->orderItemMock);

        $this->orderLogService->onOrderItemSetDeletedBy($this->orderId, $this->adminMock);
    }

    public function testItCanCallOrderTrackingWhenOrderExist()
    {
        $this->orderMock->shouldReceive('getOrderStatusLogs')
                    ->once()
                    ->andReturn(new ArrayCollection([]));

        $this->orderMock->shouldReceive('getShipments')
                    ->once()
                    ->andReturn(new ArrayCollection([]));

        $this->orderMock->shouldReceive('getOrderItems')
                    ->twice()
                    ->andReturn(new ArrayCollection([]));

        $this->orderRepositoryMock->shouldReceive('findWithTrackingLogs')
                        ->with($this->orderId)
                        ->once()
                        ->andReturn($this->orderMock);

        $this->orderLogService = new OrderLogService($this->orderRepositoryMock, $this->orderItemRepositoryMock);

        $result = $this->orderLogService->getOrderLogsTracking($this->orderId);

        self::assertIsArray($result);
        self::assertArrayHasKey('orderStatusLogs', $result);
        self::assertArrayHasKey('orderShipmentStatusLogs', $result);
        self::assertArrayHasKey('orderItemsLogs', $result);
        self::assertArrayHasKey('sellerOrderItemStatusLogs', $result);
    }
}
