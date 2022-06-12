<?php

namespace App\Tests\Unit\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;
use App\Service\Order\OrderStatus\OrderStatusService;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;
use App\Service\OrderShipment\OrderShipmentStatus\WarehouseOrderShipmentStatus;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class WarehouseOrderShipmentStatusTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|OrderShipment|MockInterface|null $orderShipmentMock;

    protected LegacyMockInterface|MockInterface|SellerOrderItemStatusService|null $sellerOrderItemStatusServiceMock;

    protected LegacyMockInterface|MockInterface|RecalculateOrderDocument|null $recalculateDocumentMock;

    protected ?WarehouseOrderShipmentStatus $preparingOrderShipmentStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderShipmentMock                = Mockery::mock(OrderShipment::class);
        $this->sellerOrderItemStatusServiceMock = Mockery::mock(SellerOrderItemStatusService::class);
        $this->recalculateDocumentMock          = Mockery::mock(RecalculateOrderDocument::class);

        $this->preparingOrderShipmentStatus = new WarehouseOrderShipmentStatus(
            Mockery::mock(OrderStatusService::class),
            $this->sellerOrderItemStatusServiceMock,
            $this->recalculateDocumentMock
        );
    }

    public function testItCanSetOrderShipmentToWaitingForSupplyWithMock(): void
    {
        $this->orderShipmentMock->shouldReceive('setStatus')
                                ->once()
                                ->with(OrderShipmentStatus::PREPARING)
                                ->andReturnSelf();

        $this->preparingOrderShipmentStatus->preparing($this->orderShipmentMock);
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
            OrderShipmentStatus::PREPARING,
            OrderShipmentStatus::CANCELED,
        ], $this->preparingOrderShipmentStatus->validTransitions());
    }

    public function supportProvider(): array
    {
        return array_map(
            static fn($status) => [$status, $status === OrderShipmentStatus::WAREHOUSE],
            OrderShipmentStatus::toArray()
        );
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
            'warehouse',
            'thirdPartyLogistics',
            'customerAbsence',
            'canceledByCustomer',
            'noSend',
        ]);
    }
}
