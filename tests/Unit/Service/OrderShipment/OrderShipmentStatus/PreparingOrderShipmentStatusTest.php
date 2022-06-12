<?php

namespace App\Tests\Unit\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\SellerOrderItem;
use App\Service\Order\OrderStatus\OrderStatusService;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;
use App\Service\OrderShipment\OrderShipmentStatus\PreparingOrderShipmentStatus;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class PreparingOrderShipmentStatusTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|OrderShipment|MockInterface|null $orderShipmentMock;

    protected LegacyMockInterface|MockInterface|SellerOrderItemStatusService|null $sellerOrderItemStatusServiceMock;

    protected LegacyMockInterface|MockInterface|RecalculateOrderDocument|null $recalculateDocumentMock;

    protected ?PreparingOrderShipmentStatus $preparingOrderShipmentStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderShipmentMock                = Mockery::mock(OrderShipment::class);
        $this->sellerOrderItemStatusServiceMock = Mockery::mock(SellerOrderItemStatusService::class);
        $this->recalculateDocumentMock          = Mockery::mock(RecalculateOrderDocument::class);

        $this->preparingOrderShipmentStatus = new PreparingOrderShipmentStatus(
            Mockery::mock(OrderStatusService::class),
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
        $this->sellerOrderItemStatusServiceMock->shouldReceive('change')
                                               ->once()
                                               ->with(
                                                   Mockery::type(SellerOrderItem::class),
                                                   SellerOrderItemStatus::WAITING_FOR_SEND
                                               )
                                               ->andReturn();

        $this->preparingOrderShipmentStatus->waitingForSupply($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToPreparedWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::PREPARED)
                                ->andReturn($this->orderShipmentMock);

        $this->preparingOrderShipmentStatus->prepared($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToWarehouseWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::WAREHOUSE)
                                ->andReturnSelf();

        $this->preparingOrderShipmentStatus->warehouse($this->orderShipmentMock);
    }

    /**
     * @dataProvider supportProvider
     */
    public function testSupport($status, $expected): void
    {
        $result = $this->preparingOrderShipmentStatus->support($status);

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

        $this->preparingOrderShipmentStatus->{$method}($this->orderShipmentMock);
    }

    public function testItCanGetValidTransitions(): void
    {
        self::assertEquals([
            OrderShipmentStatus::WAITING_FOR_SUPPLY,
            OrderShipmentStatus::PREPARED,
            OrderShipmentStatus::WAREHOUSE,
            OrderShipmentStatus::CANCELED,
        ], $this->preparingOrderShipmentStatus->validTransitions());
    }

    public function supportProvider(): array
    {
        $orderShipmentStatuses = OrderShipmentStatus::toArray();

        return array_map(function ($status) {
            return array($status, ($status === OrderShipmentStatus::PREPARING));
        }, $orderShipmentStatuses);
    }

    public function exceptionProvider(): array
    {
        return array_map(function ($method) {
            return array($method);
        }, [
            'new',
            'preparing',
            'waitingForSend',
            'sent',
            'delivered',
            'afterSales',
            'returning',
            'returned',
            'thirdPartyLogistics',
            'customerAbsence',
            'canceledByCustomer',
            'noSend',
        ]);
    }
}
