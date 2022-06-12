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
use App\Service\OrderShipment\OrderShipmentStatus\NoSendOrderShipmentStatus;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class NoSendOrderShipmentStatusTest extends BaseUnitTestCase
{
    protected Order|LegacyMockInterface|MockInterface|null $orderMock;

    protected LegacyMockInterface|OrderShipment|MockInterface|null $orderShipmentMock;

    protected OrderStatusService|LegacyMockInterface|MockInterface|null $orderStatusServiceMock;

    protected LegacyMockInterface|MockInterface|SellerOrderItemStatusService|null $sellerOrderItemStatusServiceMock;

    protected LegacyMockInterface|MockInterface|RecalculateOrderDocument|null $recalculateDocumentMock;

    protected ?NoSendOrderShipmentStatus $noSendOrderShipmentStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderMock                        = Mockery::mock(Order::class);
        $this->orderShipmentMock                = Mockery::mock(OrderShipment::class);
        $this->orderStatusServiceMock           = Mockery::mock(OrderStatusService::class);
        $this->sellerOrderItemStatusServiceMock = Mockery::mock(SellerOrderItemStatusService::class);
        $this->recalculateDocumentMock          = Mockery::mock(RecalculateOrderDocument::class);

        $this->noSendOrderShipmentStatus = new NoSendOrderShipmentStatus(
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

        $this->noSendOrderShipmentStatus->delivered($this->orderShipmentMock);
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

        $this->noSendOrderShipmentStatus->delivered($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToWaitingForSendWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::WAITING_FOR_SEND)
                                ->andReturn($this->orderShipmentMock);

        $this->noSendOrderShipmentStatus->waitingForSend($this->orderShipmentMock);
    }

    /**
     * @dataProvider supportProvider
     */
    public function testSupport($status, $expected): void
    {
        $result = $this->noSendOrderShipmentStatus->support($status);

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

        $this->noSendOrderShipmentStatus->{$method}($this->orderShipmentMock);
    }

    public function testItCanGetValidTransitions(): void
    {
        self::assertEquals([
            OrderShipmentStatus::WAITING_FOR_SEND,
            OrderShipmentStatus::DELIVERED,
            OrderShipmentStatus::CANCELED,
        ], $this->noSendOrderShipmentStatus->validTransitions());
    }

    public function supportProvider(): array
    {
        $orderShipmentStatuses = OrderShipmentStatus::toArray();

        return array_map(function ($status) {
            return array($status, ($status === OrderShipmentStatus::NO_SEND));
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
            'sent',
            'afterSales',
            'returning',
            'returned',
            'warehouse',
            'thirdPartyLogistics',
            'customerAbsence',
            'canceledByCustomer',
            'noSend',
        ]);
    }
}
