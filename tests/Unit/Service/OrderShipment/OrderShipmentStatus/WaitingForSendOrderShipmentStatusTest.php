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
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;
use App\Service\OrderShipment\OrderShipmentStatus\WaitingForSendOrderShipmentStatus;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class WaitingForSendOrderShipmentStatusTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|OrderShipment|MockInterface|null $orderShipmentMock;

    protected Order|LegacyMockInterface|MockInterface|null $orderMock;

    protected OrderStatusService|LegacyMockInterface|MockInterface|null $orderStatusServiceMock;

    protected LegacyMockInterface|MockInterface|SellerOrderItemStatusService|null $sellerOrderItemStatusServiceMock;

    protected LegacyMockInterface|MockInterface|RecalculateOrderDocument|null $recalculateDocumentMock;

    protected ?WaitingForSendOrderShipmentStatus $waitingForSendOrderShipmentStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderShipmentMock                = Mockery::mock(OrderShipment::class);
        $this->orderMock                        = Mockery::mock(Order::class);
        $this->orderStatusServiceMock           = Mockery::mock(OrderStatusService::class);
        $this->sellerOrderItemStatusServiceMock = Mockery::mock(SellerOrderItemStatusService::class);
        $this->recalculateDocumentMock          = Mockery::mock(RecalculateOrderDocument::class);

        $this->waitingForSendOrderShipmentStatus = new WaitingForSendOrderShipmentStatus(
            $this->orderStatusServiceMock,
            $this->sellerOrderItemStatusServiceMock,
            $this->recalculateDocumentMock
        );
    }

    public function testItCanSetOrderShipmentToPreparingWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::PREPARING)
                                ->andReturnSelf();
        $this->orderShipmentMock->shouldReceive('increasePackagedCount')
                                ->once()
                                ->with(0)
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
                                                   SellerOrderItemStatus::FULFILLING
                                               )
                                               ->andReturn();

        $this->waitingForSendOrderShipmentStatus->preparing($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToSentWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::SENT)
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
                                                   SellerOrderItemStatus::SENT_TO_CUSTOMER
                                               )
                                               ->andReturn();

        $this->waitingForSendOrderShipmentStatus->sent($this->orderShipmentMock);
    }

    /**
     * @dataProvider supportProvider
     */
    public function testSupport($status, $expected): void
    {
        $result = $this->waitingForSendOrderShipmentStatus->support($status);

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

        $this->waitingForSendOrderShipmentStatus->{$method}($this->orderShipmentMock);
    }

    public function testItCanGetValidTransitions(): void
    {
        self::assertEquals([
            OrderShipmentStatus::PREPARING,
            OrderShipmentStatus::SENT,
            OrderShipmentStatus::CANCELED,
        ], $this->waitingForSendOrderShipmentStatus->validTransitions());
    }

    public function supportProvider(): array
    {
        $orderShipmentStatuses = OrderShipmentStatus::toArray();

        return array_map(function ($status) {
            return array($status, ($status === OrderShipmentStatus::WAITING_FOR_SEND));
        }, $orderShipmentStatuses);
    }

    public function exceptionProvider(): array
    {
        return array_map(function ($method) {
            return array($method);
        }, [
            'new',
            'waitingForSupply',
            'prepared',
            'waitingForSend',
            'delivered',
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
