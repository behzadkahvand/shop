<?php

namespace App\Tests\Unit\Service\Order\OrderStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Entity\OrderShipment;
use App\Service\Order\OrderStatus\ConfirmedOrderStatus;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusTransitionException;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\OrderShipment\SystemChangeOrderShipmentStatus\SystemChangeOrderShipmentStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ConfirmedOrderStatusTest extends MockeryTestCase
{
    /**
     * @var Order|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $orderMock;

    /**
     * @var SystemChangeOrderShipmentStatus|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $systemChangeOrderShipmentStatusMock;

    protected ConfirmedOrderStatus $confirmedOrderStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderMock = Mockery::mock(Order::class);

        $this->systemChangeOrderShipmentStatusMock = Mockery::mock(OrderShipmentStatusService::class);

        $this->confirmedOrderStatus = new ConfirmedOrderStatus($this->systemChangeOrderShipmentStatusMock);
    }

    protected function tearDown(): void
    {
        unset($this->orderMock, $this->orderShipmentStatusService, $this->confirmedOrderStatus);
    }

    public function testItCanSetOrderToDeliveredWithMock(): void
    {
        $this->orderMock->shouldReceive('getShipments')->once()->withNoArgs()->andReturnUsing(function () {
            $shipment = Mockery::mock(OrderShipment::class);
            $shipment->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(OrderShipmentStatus::SENT);

            return new ArrayCollection([$shipment]);
        });

        $this->orderMock
            ->shouldReceive('setStatus')
            ->once()
            ->with(OrderStatus::DELIVERED)
            ->andReturnSelf();

        $this->systemChangeOrderShipmentStatusMock
            ->shouldReceive('change')
            ->once()
            ->with(Mockery::type(OrderShipment::class), OrderShipmentStatus::DELIVERED)
            ->andReturn();

        $this->confirmedOrderStatus->delivered($this->orderMock);
    }

    public function testItCanSetOrderToDeliveredWithFilteringShipment(): void
    {
        $this->orderMock->shouldReceive('getShipments')->once()->withNoArgs()->andReturnUsing(function () {
            $shipment = Mockery::mock(OrderShipment::class);
            $shipment->shouldReceive('getStatus')
                     ->times(4)
                     ->withNoArgs()
                     ->andReturn(
                         OrderShipmentStatus::SENT,
                         OrderShipmentStatus::CANCELED,
                         OrderShipmentStatus::CANCELED_BY_CUSTOMER,
                         OrderShipmentStatus::DELIVERED,
                     );

            return new ArrayCollection([$shipment, $shipment, $shipment, $shipment]);
        });

        $this->orderMock
            ->shouldReceive('setStatus')
            ->once()
            ->with(OrderStatus::DELIVERED)
            ->andReturnSelf();

        $this->systemChangeOrderShipmentStatusMock
            ->shouldReceive('change')
            ->once()
            ->with(Mockery::type(OrderShipment::class), OrderShipmentStatus::DELIVERED)
            ->andReturn();

        $this->confirmedOrderStatus->delivered($this->orderMock);
    }

    /**
     * @dataProvider supportProvider
     */
    public function testSupport($status, $assert): void
    {
        $result = $this->confirmedOrderStatus->support($status);

        self::assertEquals($assert, $result);
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testItThrowsExceptionWhenOrderStatusTransitionIsInvalid($method): void
    {
        $this->expectException(InvalidOrderStatusTransitionException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order status transition is invalid!');

        $this->confirmedOrderStatus->{$method}($this->orderMock);
    }

    public function supportProvider(): array
    {
        $orderStatuses = OrderStatus::toArray();

        return array_map(function ($status) {
            return array($status, ($status === OrderStatus::CONFIRMED));
        }, $orderStatuses);
    }

    public function testItCanGetValidTransitions(): void
    {
        $result = $this->confirmedOrderStatus->validTransitions();

        self::assertEquals([
            OrderStatus::DELIVERED,
            OrderStatus::CANCELED,
            OrderStatus::REFUND,
        ], $result);
    }

    public function exceptionProvider(): array
    {
        return array_map(function ($method) {
            return array($method);
        }, [
            'new',
            'waitCustomer',
            'callFailed',
            'waitingForPay',
            'confirmed',
            'canceledSystem',
        ]);
    }
}
