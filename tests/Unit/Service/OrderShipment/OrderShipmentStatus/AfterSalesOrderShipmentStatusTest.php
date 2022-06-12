<?php

namespace App\Tests\Unit\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\SellerOrderItem;
use App\Service\Order\OrderStatus\OrderStatusService;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\OrderShipment\OrderShipmentStatus\AfterSalesOrderShipmentStatus;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class AfterSalesOrderShipmentStatusTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|OrderShipment|MockInterface|null $orderShipmentMock;

    protected Order|LegacyMockInterface|MockInterface|null $orderMock;

    protected OrderStatusService|LegacyMockInterface|MockInterface|null $orderStatusServiceMock;

    protected LegacyMockInterface|MockInterface|SellerOrderItemStatusService|null $sellerOrderItemStatusServiceMock;

    protected LegacyMockInterface|MockInterface|RecalculateOrderDocument|null $recalculateDocumentMock;

    protected ?AfterSalesOrderShipmentStatus $afterSalesOrderShipmentStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderShipmentMock                = Mockery::mock(OrderShipment::class);
        $this->orderMock                        = Mockery::mock(Order::class);
        $this->orderStatusServiceMock           = Mockery::mock(OrderStatusService::class);
        $this->sellerOrderItemStatusServiceMock = Mockery::mock(SellerOrderItemStatusService::class);
        $this->recalculateDocumentMock          = Mockery::mock(RecalculateOrderDocument::class);

        $this->afterSalesOrderShipmentStatus = new AfterSalesOrderShipmentStatus(
            $this->orderStatusServiceMock,
            $this->sellerOrderItemStatusServiceMock,
            $this->recalculateDocumentMock
        );
    }

    public function testItCanSetOrderShipmentToWaitingForSupplyWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::WAITING_FOR_SUPPLY)
                                ->andReturnSelf();
        $this->orderShipmentMock->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturnUsing(function () {
            $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
            $sellerOrderItem->shouldReceive('getStatus')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(SellerOrderItemStatus::WAITING);

            $orderItem = Mockery::mock(OrderItem::class);
            $orderItem->shouldReceive('getSellerOrderItem')->once()->withNoArgs()->andReturn($sellerOrderItem);

            return new ArrayCollection([$orderItem]);
        });

        $this->sellerOrderItemStatusServiceMock->shouldReceive('change')
                                               ->once()
                                               ->with(
                                                   Mockery::type(SellerOrderItem::class),
                                                   SellerOrderItemStatus::WAITING_FOR_SEND
                                               )
                                               ->andReturn();

        $this->afterSalesOrderShipmentStatus->waitingForSupply($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToWaitingForSendWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::WAITING_FOR_SEND)
                                ->andReturnSelf();

        $this->afterSalesOrderShipmentStatus->waitingForSend($this->orderShipmentMock);
    }

    /**
     * @dataProvider supportProvider
     */
    public function testSupport($status, $expected): void
    {
        $result = $this->afterSalesOrderShipmentStatus->support($status);

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testItThrowsExceptionWhenOrderStatusTransitionIsInvalid($method): void
    {
        $this->expectException(InvalidOrderShipmentStatusTransitionException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order shipment status transition is invalid!');

        $this->afterSalesOrderShipmentStatus->{$method}($this->orderShipmentMock);
    }

    public function testItCanGetValidTransitions(): void
    {
        self::assertEquals([
            OrderShipmentStatus::WAITING_FOR_SUPPLY,
            OrderShipmentStatus::WAITING_FOR_SEND,
            OrderShipmentStatus::CANCELED,
        ], $this->afterSalesOrderShipmentStatus->validTransitions());
    }

    public function supportProvider(): array
    {
        $orderShipmentStatuses = OrderShipmentStatus::toArray();

        return array_map(function ($status) {
            return [$status, ($status === OrderShipmentStatus::AFTER_SALES)];
        }, $orderShipmentStatuses);
    }

    public function exceptionProvider(): array
    {
        return array_map(function ($method) {
            return [$method];
        }, [
            'new',
            'preparing',
            'prepared',
            'sent',
            'delivered',
            'afterSales',
            'returning',
            'returned',
            'thirdPartyLogistics',
            'warehouse',
            'customerAbsence',
            'canceledByCustomer',
            'noSend',
        ]);
    }
}
