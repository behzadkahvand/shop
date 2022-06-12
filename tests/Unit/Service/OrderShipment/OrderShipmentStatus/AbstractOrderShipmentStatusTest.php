<?php

namespace App\Tests\Unit\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\SellerOrderItem;
use App\Service\Order\OrderStatus\OrderStatusService;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\OrderShipment\OrderShipmentStatus\AbstractOrderShipmentStatus;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class AbstractOrderShipmentStatusTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|OrderShipment|MockInterface|null $orderShipmentMock;

    protected Order|LegacyMockInterface|MockInterface|null $orderMock;

    protected OrderStatusService|LegacyMockInterface|MockInterface|null $orderStatusServiceMock;

    protected LegacyMockInterface|SellerOrderItemStatusService|MockInterface|null $sellerOrderItemStatusServiceMock;

    protected LegacyMockInterface|MockInterface|RecalculateOrderDocument|null $recalculateDocumentMock;

    protected ?AbstractOrderShipmentStatus $orderShipmentStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderShipmentMock                = Mockery::mock(OrderShipment::class);
        $this->orderMock                        = Mockery::mock(Order::class);
        $this->orderStatusServiceMock           = Mockery::mock(OrderStatusService::class);
        $this->sellerOrderItemStatusServiceMock = Mockery::mock(SellerOrderItemStatusService::class);
        $this->recalculateDocumentMock          = Mockery::mock(RecalculateOrderDocument::class);

        $args = [$this->orderStatusServiceMock, $this->sellerOrderItemStatusServiceMock, $this->recalculateDocumentMock];

        $this->orderShipmentStatus = new class (...$args) extends AbstractOrderShipmentStatus {
            public function support(string $status): bool
            {
                return true;
            }

            public function validTransitions(): array
            {
                return [];
            }
        };
    }

    /**
     * @dataProvider provider
     */
    public function testAbstractOrderStatus($method): void
    {
        $this->expectException(InvalidOrderShipmentStatusTransitionException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order shipment status transition is invalid!');

        $this->orderShipmentStatus->{$method}($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToCancelWhenOrderShouldBeCanceled(): void
    {
        $this->orderShipmentMock->expects('setStatus')
                                ->with(OrderShipmentStatus::CANCELED)
                                ->andReturnSelf();

        $this->orderShipmentMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->orderShipmentMock->expects('getOrderItems')->withNoArgs()->andReturnUsing(function () {
            $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
            $sellerOrderItem->shouldReceive('getStatus')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(SellerOrderItemStatus::WAITING);

            $orderItem = Mockery::mock(OrderItem::class);
            $orderItem->shouldReceive('getSellerOrderItem')->once()->withNoArgs()->andReturn($sellerOrderItem);

            return new ArrayCollection([$orderItem]);
        });

        $this->orderMock->expects('getShipments')->withNoArgs()->andReturns(new ArrayCollection([
            $this->orderShipmentMock,
            $this->orderShipmentMock
        ]));

        $this->orderMock->expects('getStatus')->withNoArgs()->andReturns(OrderStatus::CALL_FAILED);

        $this->orderShipmentMock->expects('getStatus')
                                ->twice()
                                ->withNoArgs()
                                ->andReturns(OrderShipmentStatus::CANCELED, OrderShipmentStatus::CANCELED);

        $this->orderStatusServiceMock->expects('change')->with($this->orderMock, OrderStatus::CANCELED);
        $this->sellerOrderItemStatusServiceMock->expects('change')
                                               ->with(
                                                   Mockery::type(SellerOrderItem::class),
                                                   SellerOrderItemStatus::CANCELED_BY_USER
                                               )
                                               ->andReturns();

        $this->recalculateDocumentMock->expects('perform')->with($this->orderMock)->andReturns();

        $this->orderShipmentStatus->canceled($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToCancelWhenOrderShouldBeDelivered(): void
    {
        $this->orderShipmentMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->orderShipmentMock->expects('setStatus')
                                ->with(OrderShipmentStatus::CANCELED)
                                ->andReturnSelf();

        $this->orderMock->expects('getShipments')->withNoArgs()->andReturns(new ArrayCollection([
            $this->orderShipmentMock,
            $this->orderShipmentMock
        ]));

        $this->orderShipmentMock->expects('isCanceled')
                                ->twice()
                                ->withNoArgs()
                                ->andReturns(true, false);

        $this->orderShipmentMock->expects('isCanceledByCustomer')
                                ->withNoArgs()
                                ->andReturnFalse();
        $this->orderShipmentMock->expects('isDelivered')
                                ->withNoArgs()
                                ->andReturnTrue();

        $this->orderShipmentMock->expects('getOrderItems')->withNoArgs()->andReturnUsing(function () {
            $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
            $sellerOrderItem->expects('getStatus')
                            ->withNoArgs()
                            ->andReturns(SellerOrderItemStatus::WAITING);

            $orderItem = Mockery::mock(OrderItem::class);
            $orderItem->expects('getSellerOrderItem')->withNoArgs()->andReturns($sellerOrderItem);

            return new ArrayCollection([$orderItem]);
        });

        $this->orderStatusServiceMock->expects('change')->with($this->orderMock, OrderStatus::DELIVERED);

        $this->sellerOrderItemStatusServiceMock->expects('change')
                                               ->with(
                                                   Mockery::type(SellerOrderItem::class),
                                                   SellerOrderItemStatus::CANCELED_BY_USER
                                               )
                                               ->andReturns();

        $this->recalculateDocumentMock->expects('perform')->with($this->orderMock)->andReturns();

        $this->orderMock->expects('getStatus')->withNoArgs()->andReturns(OrderStatus::CANCELED);

        $this->orderShipmentStatus->canceled($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToCancelWithNoOrderSave(): void
    {
        $this->orderShipmentMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->orderShipmentMock->expects('setStatus')
                                ->with(OrderShipmentStatus::CANCELED)
                                ->andReturnSelf();

        $this->orderMock->expects('getShipments')->withNoArgs()->andReturns(new ArrayCollection([
            $this->orderShipmentMock,
            $this->orderShipmentMock
        ]));

        $this->orderShipmentMock->expects('isCanceled')
                                ->twice()
                                ->withNoArgs()
                                ->andReturns(true, false);

        $this->orderShipmentMock->expects('isCanceledByCustomer')
                                ->withNoArgs()
                                ->andReturnFalse();
        $this->orderShipmentMock->expects('isDelivered')
                                ->withNoArgs()
                                ->andReturnFalse();

        $this->orderShipmentMock->expects('getOrderItems')->withNoArgs()->andReturnUsing(function () {
            $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
            $sellerOrderItem->expects('getStatus')
                            ->withNoArgs()
                            ->andReturns(SellerOrderItemStatus::WAITING);

            $orderItem = Mockery::mock(OrderItem::class);
            $orderItem->expects('getSellerOrderItem')->withNoArgs()->andReturns($sellerOrderItem);

            return new ArrayCollection([$orderItem]);
        });

        $this->orderStatusServiceMock->allows('change')->never();

        $this->sellerOrderItemStatusServiceMock->expects('change')
                                               ->with(
                                                   Mockery::type(SellerOrderItem::class),
                                                   SellerOrderItemStatus::CANCELED_BY_USER
                                               )
                                               ->andReturns();
        $this->recalculateDocumentMock->expects('perform')->with($this->orderMock)->andReturns();

        $this->orderMock->expects('getStatus')->withNoArgs()->andReturns(OrderStatus::CANCELED);

        $this->orderShipmentStatus->canceled($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToCancelForOrderThatCanceledBySystem(): void
    {
        $this->orderShipmentMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->orderShipmentMock->expects('setStatus')
                                ->with(OrderShipmentStatus::CANCELED)
                                ->andReturnSelf();

        $this->orderMock->expects('getShipments')->withNoArgs()->andReturns(new ArrayCollection([
            $this->orderShipmentMock,
            $this->orderShipmentMock
        ]));

        $this->orderShipmentMock->expects('isCanceled')
                                ->twice()
                                ->withNoArgs()
                                ->andReturns(true, false);

        $this->orderShipmentMock->expects('isCanceledByCustomer')
                                ->withNoArgs()
                                ->andReturns(false);
        $this->orderShipmentMock->expects('isDelivered')
                                ->withNoArgs()
                                ->andReturns(false);

        $this->orderShipmentMock->expects('getOrderItems')->withNoArgs()->andReturnUsing(function () {
            $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
            $sellerOrderItem->shouldReceive('getStatus')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(SellerOrderItemStatus::WAITING);

            $orderItem = Mockery::mock(OrderItem::class);
            $orderItem->shouldReceive('getSellerOrderItem')->once()->withNoArgs()->andReturn($sellerOrderItem);

            return new ArrayCollection([$orderItem]);
        });

        $this->orderStatusServiceMock->allows('change')->never();

        $this->sellerOrderItemStatusServiceMock->expects('change')
                                               ->with(
                                                   Mockery::type(SellerOrderItem::class),
                                                   SellerOrderItemStatus::CANCELED_BY_USER
                                               )
                                               ->andReturns();

        $this->recalculateDocumentMock->expects('perform')->with($this->orderMock)->andReturns();

        $this->orderMock->expects('getStatus')->withNoArgs()->andReturns(OrderStatus::CANCELED_SYSTEM);

        $this->orderShipmentStatus->canceled($this->orderShipmentMock);
    }

    public function provider(): array
    {
        $methods = get_class_methods(AbstractOrderShipmentStatus::class);

        unset(
            $methods[array_search('__construct', $methods, true)],
            $methods[array_search('support', $methods, true)],
            $methods[array_search('canceled', $methods, true)],
            $methods[array_search('validTransitions', $methods, true)]
        );

        return array_map(static fn($method) => array($method), $methods);
    }
}
