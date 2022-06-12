<?php

namespace App\Tests\Unit\Service\SellerDashboard;

use App\Dictionary\SellerOrderItemStatus;
use App\Entity\Seller;
use App\Repository\InventoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\SellerOrderItemRepository;
use App\Repository\SellerRepository;
use App\Service\SellerDashboard\SellerDashboardService;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SellerDashboardServiceTest extends MockeryTestCase
{
    /**
     * @var SellerRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $sellerRepoMock;

    /**
     * @var SellerOrderItemRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderItemRepoMock;

    /**
     * @var InventoryRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $inventoryRepoMock;

    /**
     * @var OrderRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $orderRepoMock;

    /**
     * @var ProductRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $productRepoMock;

    /**
     * @var Seller|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $sellerMock;

    protected string $nowDate;

    protected SellerDashboardService $sellerDashboardService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerRepoMock    = Mockery::mock(SellerRepository::class);
        $this->orderItemRepoMock = Mockery::mock(SellerOrderItemRepository::class);
        $this->inventoryRepoMock = Mockery::mock(InventoryRepository::class);
        $this->orderRepoMock     = Mockery::mock(OrderRepository::class);
        $this->productRepoMock   = Mockery::mock(ProductRepository::class);

        $this->sellerMock        = Mockery::mock(Seller::class);

        $this->nowDate = (new DateTimeImmutable('now'))->format('Y-m-d');

        $this->sellerDashboardService = new SellerDashboardService(
            $this->sellerRepoMock,
            $this->orderItemRepoMock,
            $this->inventoryRepoMock,
            $this->orderRepoMock,
            $this->productRepoMock
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->nowDate,
            $this->sellerDashboardService
        );

        $this->sellerRepoMock = null;
        $this->orderItemRepoMock = null;
        $this->inventoryRepoMock = null;
        $this->productRepoMock = null;
        $this->orderRepoMock = null;
        $this->sellerMock = null;
    }

    public function testItCanGetSellerDashboardData()
    {
        $this->sellerMock->shouldReceive('getName')
                         ->once()
                         ->withNoArgs()
                         ->andReturn('Seller-name');
        $this->sellerMock->shouldReceive('getUsername')
                         ->once()
                         ->withNoArgs()
                         ->andReturn('seller@email.com');

        $this->orderItemRepoMock->shouldReceive('countOrderItems')
                                ->once()
                                ->with(
                                    $this->sellerMock,
                                    SellerOrderItemStatus::WAITING_FOR_SEND
                                )
                                ->andReturn(10);
        $this->orderItemRepoMock->shouldReceive('countOrderItems')
                                ->once()
                                ->with(
                                    $this->sellerMock,
                                    SellerOrderItemStatus::WAITING_FOR_SEND,
                                    $this->nowDate,
                                    '<'
                                )
                                ->andReturn(5);
        $this->orderItemRepoMock->shouldReceive('countOrderItems')
                                ->once()
                                ->with(
                                    $this->sellerMock,
                                    SellerOrderItemStatus::SENT_BY_SELLER,
                                    $this->nowDate,
                                    '='
                                )
                                ->andReturn(20);
        $this->orderItemRepoMock->shouldReceive('countOrderItems')
                                ->once()
                                ->with(
                                    $this->sellerMock,
                                    SellerOrderItemStatus::WAITING_FOR_SEND,
                                    $this->nowDate,
                                    '>'
                                )
                                ->andReturn(3);

        $this->orderRepoMock->shouldReceive('countDeliveredOrConfirmedOrderForSeller')
                                ->once()
                                ->with(
                                    $this->sellerMock,
                                    $this->nowDate,
                                    '='
                                )
                                ->andReturn(2);

        $this->inventoryRepoMock->shouldReceive('getCountBySellerAndStatus')
                                ->once()
                                ->with($this->sellerMock)
                                ->andReturn(50);
        $this->inventoryRepoMock->shouldReceive('getCountBySellerAndStatus')
                                ->once()
                                ->with($this->sellerMock, false)
                                ->andReturn(10);
        $this->inventoryRepoMock->shouldReceive('getCountActiveWithoutStockBySeller')
                                ->once()
                                ->with($this->sellerMock)
                                ->andReturn(5);

        $this->productRepoMock->shouldReceive('getCountBuyBoxForSeller')
                                ->once()
                                ->with($this->sellerMock)
                                ->andReturn(5);

        $this->orderItemRepoMock->shouldReceive('soldOrderItems')
                                ->once()
                                ->with(
                                    $this->sellerMock,
                                    'soldSellerItems-lastSevenDays',
                                    (new DateTimeImmutable('-7 day'))->format('Y-m-d H:i:s')
                                )
                                ->andReturn([
                                    'count' => 5,
                                    'total' => 100000
                                ]);
        $this->orderItemRepoMock->shouldReceive('soldOrderItems')
                                ->once()
                                ->with(
                                    $this->sellerMock,
                                    'soldSellerItems-lastThirtyDays',
                                    (new DateTimeImmutable('-30 day'))->format('Y-m-d H:i:s')
                                )
                                ->andReturn([
                                    'count' => 18,
                                    'total' => 530000
                                ]);
        $this->orderItemRepoMock->shouldReceive('soldOrderItems')
                                ->once()
                                ->with(
                                    $this->sellerMock,
                                    'soldSellerItems'
                                )
                                ->andReturn([
                                    'count' => 55,
                                    'total' => 1070000
                                ]);

        $result = $this->sellerDashboardService->get($this->sellerMock);

        self::assertArrayHasKey('seller', $result);
        self::assertArrayHasKey('orderItems', $result);
        self::assertArrayHasKey('inventories', $result);
        self::assertArrayHasKey('soldItems', $result);

        $seller = $result['seller'];
        self::assertArrayHasKey('name', $seller);
        self::assertArrayHasKey('userName', $seller);

        $orderItems = $result['orderItems'];
        self::assertArrayHasKey('waitingForSend', $orderItems);
        self::assertArrayHasKey('delayed', $orderItems);
        self::assertArrayHasKey('sent', $orderItems);
        self::assertArrayHasKey('futureWaitingForSend', $orderItems);
        self::assertArrayHasKey('todayWaitingForSend', $orderItems);

        $inventories = $result['inventories'];
        self::assertArrayHasKey('active', $inventories);
        self::assertArrayHasKey('inactive', $inventories);
        self::assertArrayHasKey('withoutStock', $inventories);
        self::assertArrayHasKey('buyBox', $inventories);

        $soldItems = $result['soldItems'];
        self::assertArrayHasKey('lastSevenDays', $soldItems);
        self::assertArrayHasKey('lastThirtyDays', $soldItems);
        self::assertArrayHasKey('all', $soldItems);
    }
}
