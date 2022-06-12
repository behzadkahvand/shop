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
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;
use App\Service\OrderShipment\OrderShipmentStatus\SentOrderShipmentStatus;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class SentOrderShipmentStatusTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|OrderShipment|MockInterface|null $orderShipmentMock;

    protected Order|LegacyMockInterface|MockInterface|null $orderMock;

    protected OrderStatusService|LegacyMockInterface|MockInterface|null $orderStatusServiceMock;

    protected LegacyMockInterface|MockInterface|SellerOrderItemStatusService|null $sellerOrderItemStatusServiceMock;

    protected LegacyMockInterface|MockInterface|RecalculateOrderDocument|null $recalculateDocumentMock;

    protected ?SentOrderShipmentStatus $sentOrderShipmentStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderMock                        = Mockery::mock(Order::class);
        $this->orderShipmentMock                = Mockery::mock(OrderShipment::class);
        $this->orderStatusServiceMock           = Mockery::mock(OrderStatusService::class);
        $this->sellerOrderItemStatusServiceMock = Mockery::mock(SellerOrderItemStatusService::class);
        $this->recalculateDocumentMock          = Mockery::mock(RecalculateOrderDocument::class);

        $this->sentOrderShipmentStatus = new SentOrderShipmentStatus(
            $this->orderStatusServiceMock,
            $this->sellerOrderItemStatusServiceMock,
            $this->recalculateDocumentMock
        );
    }

    public function testItCanSetOrderShipmentToDeliveredWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::DELIVERED)
                                ->andReturnSelf();
        $this->orderShipmentMock->shouldReceive('getOrderItems')
                                ->once()
                                ->withNoArgs()
                                ->andReturnUsing(function () {
                                    $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
                                    $sellerOrderItem->shouldReceive('getStatus')
                                                    ->once()
                                                    ->withNoArgs()
                                                    ->andReturn(SellerOrderItemStatus::WAITING);

                                    $orderItem = Mockery::mock(OrderItem::class);
                                    $orderItem->shouldReceive('getSellerOrderItem')
                                              ->once()
                                              ->withNoArgs()
                                              ->andReturn($sellerOrderItem);

                                    return new ArrayCollection([$orderItem]);
                                });

        $this->orderShipmentMock->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($this->orderMock);
        $this->orderShipmentMock->shouldReceive('isCanceled')
                                ->times(3)
                                ->withNoArgs()
                                ->andReturn(false, true, false);
        $this->orderShipmentMock->shouldReceive('isCanceledByCustomer')
                                ->twice()
                                ->withNoArgs()
                                ->andReturn(false, true);
        $this->orderShipmentMock->shouldReceive('isDelivered')->once()->withNoArgs()->andReturnTrue();
        $this->orderMock->shouldReceive('getShipments')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$this->orderShipmentMock, $this->orderShipmentMock, $this->orderShipmentMock]));

        $this->sellerOrderItemStatusServiceMock->shouldReceive('change')
                                               ->once()
                                               ->with(
                                                   Mockery::type(SellerOrderItem::class),
                                                   SellerOrderItemStatus::DELIVERED
                                               )
                                               ->andReturn();

        $this->orderStatusServiceMock->shouldReceive('change')
                                     ->once()
                                     ->with($this->orderMock, OrderStatus::DELIVERED)
                                     ->andReturn();

        $this->sentOrderShipmentStatus->delivered($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToDeliveredWithNoOrderSave(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::DELIVERED)
                                ->andReturnSelf();
        $this->orderShipmentMock->shouldReceive('getOrderItems')
                                ->once()
                                ->withNoArgs()
                                ->andReturnUsing(function () {
                                    $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
                                    $sellerOrderItem->shouldReceive('getStatus')
                                                    ->once()
                                                    ->withNoArgs()
                                                    ->andReturn(SellerOrderItemStatus::WAITING);

                                    $orderItem = Mockery::mock(OrderItem::class);
                                    $orderItem->shouldReceive('getSellerOrderItem')
                                              ->once()
                                              ->withNoArgs()
                                              ->andReturn($sellerOrderItem);

                                    return new ArrayCollection([$orderItem]);
                                });

        $this->orderShipmentMock->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($this->orderMock);
        $this->orderShipmentMock->shouldReceive('isCanceled')->once()->withNoArgs()->andReturnFalse();
        $this->orderShipmentMock->shouldReceive('isCanceledByCustomer')->once()->withNoArgs()->andReturnFalse();
        $this->orderShipmentMock->shouldReceive('isDelivered')->once()->withNoArgs()->andReturnTrue();
        $this->orderMock->shouldReceive('getShipments')
                        ->once()
                        ->withNoArgs()
                        ->andReturnUsing(function () {
                            $shipment = Mockery::mock(OrderShipment::class);
                            $shipment->shouldReceive('isCanceled')->once()->withNoArgs()->andReturnFalse();
                            $shipment->shouldReceive('isCanceledByCustomer')->once()->withNoArgs()->andReturnFalse();
                            $shipment->shouldReceive('isDelivered')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnFalse();

                            return new ArrayCollection([$this->orderShipmentMock, $shipment]);
                        });

        $this->sellerOrderItemStatusServiceMock->shouldReceive('change')
                                               ->once()
                                               ->with(
                                                   Mockery::type(SellerOrderItem::class),
                                                   SellerOrderItemStatus::DELIVERED
                                               )
                                               ->andReturn();

        $this->orderMock->shouldNotReceive('setStatus');

        $this->sentOrderShipmentStatus->delivered($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToReturningWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::RETURNING)
                                ->andReturnSelf();

        $this->sentOrderShipmentStatus->returning($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToThirdPartyLogisticsWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::THIRD_PARTY_LOGISTICS)
                                ->andReturnSelf();

        $this->sentOrderShipmentStatus->thirdPartyLogistics($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToCustomerAbsenceWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::CUSTOMER_ABSENCE)
                                ->andReturnSelf();

        $this->sentOrderShipmentStatus->customerAbsence($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToCanceledByCustomerWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::CANCELED_BY_CUSTOMER)
                                ->andReturnSelf();

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

        $this->sellerOrderItemStatusServiceMock->expects('change')
                                               ->with(
                                                   Mockery::type(SellerOrderItem::class),
                                                   SellerOrderItemStatus::CANCELED_BY_USER
                                               )
                                               ->andReturns();

        $this->orderShipmentMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);

        $this->recalculateDocumentMock->expects('perform')->with($this->orderMock)->andReturns();

        $this->sentOrderShipmentStatus->canceledByCustomer($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToNoSendWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::NO_SEND)
                                ->andReturnSelf();

        $this->sentOrderShipmentStatus->noSend($this->orderShipmentMock);
    }

    /**
     * @dataProvider supportProvider
     */
    public function testSupport($status, $expected): void
    {
        $result = $this->sentOrderShipmentStatus->support($status);

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

        $this->sentOrderShipmentStatus->{$method}($this->orderShipmentMock);
    }

    public function testItCanGetValidTransitions(): void
    {
        self::assertEquals([
            OrderShipmentStatus::DELIVERED,
            OrderShipmentStatus::RETURNING,
            OrderShipmentStatus::THIRD_PARTY_LOGISTICS,
            OrderShipmentStatus::CANCELED,
            OrderShipmentStatus::CUSTOMER_ABSENCE,
            OrderShipmentStatus::CANCELED_BY_CUSTOMER,
            OrderShipmentStatus::NO_SEND,
        ], $this->sentOrderShipmentStatus->validTransitions());
    }

    public function supportProvider(): array
    {
        $orderShipmentStatuses = OrderShipmentStatus::toArray();

        return array_map(function ($status) {
            return array($status, ($status === OrderShipmentStatus::SENT));
        }, $orderShipmentStatuses);
    }

    public function exceptionProvider(): array
    {
        return array_map(function ($method) {
            return array($method);
        }, [
            'new',
            'waitingForSupply',
            'preparing',
            'prepared',
            'waitingForSend',
            'sent',
            'afterSales',
            'returned',
            'warehouse',
        ]);
    }
}
