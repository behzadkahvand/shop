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
use App\Service\OrderShipment\OrderShipmentStatus\PreparedOrderShipmentStatus;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class PreparedOrderShipmentStatusTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|OrderShipment|MockInterface|null $orderShipmentMock;

    protected LegacyMockInterface|MockInterface|SellerOrderItemStatusService|null $sellerOrderItemStatusService;

    protected LegacyMockInterface|MockInterface|RecalculateOrderDocument|null $recalculateDocumentMock;

    protected ?PreparedOrderShipmentStatus $preparedOrderShipmentStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderShipmentMock            = Mockery::mock(OrderShipment::class);
        $this->sellerOrderItemStatusService = Mockery::mock(SellerOrderItemStatusService::class);
        $this->recalculateDocumentMock      = Mockery::mock(RecalculateOrderDocument::class);

        $this->preparedOrderShipmentStatus = new PreparedOrderShipmentStatus(
            Mockery::mock(OrderStatusService::class),
            $this->sellerOrderItemStatusService,
            $this->recalculateDocumentMock
        );
    }

    public function testItCanSetOrderShipmentToPreparingWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::PREPARING)
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

        $this->sellerOrderItemStatusService->shouldReceive('change')
                                           ->once()
                                           ->with(
                                               Mockery::type(SellerOrderItem::class),
                                               SellerOrderItemStatus::FULFILLING
                                           )
                                           ->andReturn();

        $this->preparedOrderShipmentStatus->preparing($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToPackagedWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::PACKAGED)
                                ->andReturn($this->orderShipmentMock);
        $this->orderShipmentMock->shouldReceive('increasePackagedCount')
                                ->once()
                                ->withNoArgs()
                                ->andReturn($this->orderShipmentMock);

        $this->preparedOrderShipmentStatus->packaged($this->orderShipmentMock);
    }

    public function testItCanSetOrderShipmentToWarehouseWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::WAREHOUSE)
                                ->andReturnSelf();

        $this->preparedOrderShipmentStatus->warehouse($this->orderShipmentMock);
    }

    /**
     * @dataProvider supportProvider
     */
    public function testSupport($status, $expected): void
    {
        $result = $this->preparedOrderShipmentStatus->support($status);

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

        $this->preparedOrderShipmentStatus->{$method}($this->orderShipmentMock);
    }

    public function testItCanGetValidTransitions(): void
    {
        self::assertEquals([
            OrderShipmentStatus::PREPARING,
            OrderShipmentStatus::PACKAGED,
            OrderShipmentStatus::WAREHOUSE,
            OrderShipmentStatus::CANCELED,
        ], $this->preparedOrderShipmentStatus->validTransitions());
    }

    public function supportProvider(): array
    {
        $orderShipmentStatuses = OrderShipmentStatus::toArray();

        return array_map(function ($status) {
            return array($status, ($status === OrderShipmentStatus::PREPARED));
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
