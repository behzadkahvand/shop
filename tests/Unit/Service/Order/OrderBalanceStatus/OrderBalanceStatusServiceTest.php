<?php

namespace App\Tests\Unit\Service\Order\OrderBalanceStatus;

use App\Dictionary\OrderBalanceStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Repository\OrderDocumentRepository;
use App\Repository\OrderRepository;
use App\Repository\RefundDocumentRepository;
use App\Service\Order\OrderBalanceStatus\OrderBalanceStatusService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OrderBalanceStatusServiceTest extends MockeryTestCase
{
    /**
     * @var OrderRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderRepoMock;

    /**
     * @var OrderDocumentRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderDocumentRepoMock;

    /**
     * @var RefundDocumentRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $refundDocumentRepoMock;

    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderMock;

    protected OrderBalanceStatusService $orderBalanceStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepoMock          = Mockery::mock(OrderRepository::class);
        $this->orderDocumentRepoMock  = Mockery::mock(OrderDocumentRepository::class);
        $this->refundDocumentRepoMock = Mockery::mock(RefundDocumentRepository::class);
        $this->orderMock              = Mockery::mock(Order::class);

        $this->orderBalanceStatus = new OrderBalanceStatusService(
            $this->orderRepoMock,
            $this->orderDocumentRepoMock,
            $this->refundDocumentRepoMock
        );
    }

    protected function tearDown(): void
    {
        unset($this->orderBalanceStatus);

        $this->orderRepoMock          = null;
        $this->orderDocumentRepoMock  = null;
        $this->refundDocumentRepoMock = null;
        $this->orderMock              = null;
    }

    public function testItCanGetOrderBalanceWhenStatusIsBalance()
    {
        $orderId = 1;

        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with($orderId)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::DELIVERED);

        $this->orderDocumentRepoMock->shouldReceive('getOrderDocumentData')
                                    ->once()
                                    ->with($orderId)
                                    ->andReturn([
                                        'orderDocumentAmount'     => 1000000,
                                        'totalTransactionAmounts' => 1300000
                                    ]);

        $this->refundDocumentRepoMock->shouldReceive('getOrderRefundDocumentsData')
                                     ->once()
                                     ->with($orderId)
                                     ->andReturn([
                                         'totalOrderRefundDocumentAmounts' => 300000,
                                         'totalRefundTransactionAmounts'   => 300000
                                     ]);

        $result = $this->orderBalanceStatus->get($orderId);

        self::assertArrayHasKey('orderDocumentAmount', $result);
        self::assertArrayHasKey('totalTransactionAmounts', $result);
        self::assertArrayHasKey('totalOrderRefundDocumentAmounts', $result);
        self::assertArrayHasKey('totalRefundTransactionAmounts', $result);
        self::assertArrayHasKey('balanceAmount', $result);
        self::assertArrayHasKey('balanceStatus', $result);

        self::assertEquals(1000000, $result['orderDocumentAmount']);
        self::assertEquals(1300000, $result['totalTransactionAmounts']);
        self::assertEquals(300000, $result['totalOrderRefundDocumentAmounts']);
        self::assertEquals(300000, $result['totalRefundTransactionAmounts']);
        self::assertEquals(0, $result['balanceAmount']);
        self::assertEquals(OrderBalanceStatus::BALANCE, $result['balanceStatus']);
    }

    public function testItCanGetOrderBalanceWhenStatusIsCreditorAndOrderStatusDelivered()
    {
        $orderId = 1;

        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with($orderId)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::DELIVERED);

        $this->orderDocumentRepoMock->shouldReceive('getOrderDocumentData')
                                    ->once()
                                    ->with($orderId)
                                    ->andReturn([
                                        'orderDocumentAmount'     => 1000000,
                                        'totalTransactionAmounts' => 1300000
                                    ]);

        $this->refundDocumentRepoMock->shouldReceive('getOrderRefundDocumentsData')
                                     ->once()
                                     ->with($orderId)
                                     ->andReturn([
                                         'totalOrderRefundDocumentAmounts' => 0,
                                         'totalRefundTransactionAmounts'   => 0
                                     ]);

        $result = $this->orderBalanceStatus->get($orderId);

        self::assertArrayHasKey('orderDocumentAmount', $result);
        self::assertArrayHasKey('totalTransactionAmounts', $result);
        self::assertArrayHasKey('totalOrderRefundDocumentAmounts', $result);
        self::assertArrayHasKey('totalRefundTransactionAmounts', $result);
        self::assertArrayHasKey('balanceAmount', $result);
        self::assertArrayHasKey('balanceStatus', $result);

        self::assertEquals(1000000, $result['orderDocumentAmount']);
        self::assertEquals(1300000, $result['totalTransactionAmounts']);
        self::assertEquals(0, $result['totalOrderRefundDocumentAmounts']);
        self::assertEquals(0, $result['totalRefundTransactionAmounts']);
        self::assertEquals(300000, $result['balanceAmount']);
        self::assertEquals(OrderBalanceStatus::CREDITOR, $result['balanceStatus']);
    }

    public function testItCanGetOrderBalanceWhenStatusIsCreditorAndOrderStatusCanceled()
    {
        $orderId = 1;

        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with($orderId)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::CANCELED);

        $this->orderDocumentRepoMock->shouldReceive('getOrderDocumentData')
                                    ->once()
                                    ->with($orderId)
                                    ->andReturn([
                                        'orderDocumentAmount'     => 1000000,
                                        'totalTransactionAmounts' => 1300000
                                    ]);

        $this->refundDocumentRepoMock->shouldReceive('getOrderRefundDocumentsData')
                                     ->once()
                                     ->with($orderId)
                                     ->andReturn([
                                         'totalOrderRefundDocumentAmounts' => 300000,
                                         'totalRefundTransactionAmounts'   => 300000
                                     ]);

        $result = $this->orderBalanceStatus->get($orderId);

        self::assertArrayHasKey('orderDocumentAmount', $result);
        self::assertArrayHasKey('totalTransactionAmounts', $result);
        self::assertArrayHasKey('totalOrderRefundDocumentAmounts', $result);
        self::assertArrayHasKey('totalRefundTransactionAmounts', $result);
        self::assertArrayHasKey('balanceAmount', $result);
        self::assertArrayHasKey('balanceStatus', $result);

        self::assertEquals(1000000, $result['orderDocumentAmount']);
        self::assertEquals(1300000, $result['totalTransactionAmounts']);
        self::assertEquals(300000, $result['totalOrderRefundDocumentAmounts']);
        self::assertEquals(300000, $result['totalRefundTransactionAmounts']);
        self::assertEquals(1000000, $result['balanceAmount']);
        self::assertEquals(OrderBalanceStatus::CREDITOR, $result['balanceStatus']);
    }

    public function testItCanGetOrderBalanceWhenStatusIsDebtor()
    {
        $orderId = 1;

        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with($orderId)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::DELIVERED);

        $this->orderDocumentRepoMock->shouldReceive('getOrderDocumentData')
                                    ->once()
                                    ->with($orderId)
                                    ->andReturn([
                                        'orderDocumentAmount'     => 1000000,
                                        'totalTransactionAmounts' => 0
                                    ]);

        $this->refundDocumentRepoMock->shouldReceive('getOrderRefundDocumentsData')
                                     ->once()
                                     ->with($orderId)
                                     ->andReturn([
                                         'totalOrderRefundDocumentAmounts' => 0,
                                         'totalRefundTransactionAmounts'   => 0
                                     ]);

        $result = $this->orderBalanceStatus->get($orderId);

        self::assertArrayHasKey('orderDocumentAmount', $result);
        self::assertArrayHasKey('totalTransactionAmounts', $result);
        self::assertArrayHasKey('totalOrderRefundDocumentAmounts', $result);
        self::assertArrayHasKey('totalRefundTransactionAmounts', $result);
        self::assertArrayHasKey('balanceAmount', $result);
        self::assertArrayHasKey('balanceStatus', $result);

        self::assertEquals(1000000, $result['orderDocumentAmount']);
        self::assertEquals(0, $result['totalTransactionAmounts']);
        self::assertEquals(0, $result['totalOrderRefundDocumentAmounts']);
        self::assertEquals(0, $result['totalRefundTransactionAmounts']);
        self::assertEquals(1000000, $result['balanceAmount']);
        self::assertEquals(OrderBalanceStatus::DEBTOR, $result['balanceStatus']);
    }
}
