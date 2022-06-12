<?php

namespace App\Tests\Unit\Service\OrderShipment\SystemChangeOrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\Order;
use App\Entity\OrderShipment;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\OrderShipment\SystemChangeOrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusException;
use App\Service\OrderShipment\SystemChangeOrderShipmentStatus\SystemChangeOrderShipmentStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SystemChangeOrderShipmentStatusTest extends MockeryTestCase
{
    public function testItThrowExceptionIfOrderShipmentStatusIsNotValid()
    {
        $orderShipmentStatusService = \Mockery::mock(OrderShipmentStatusService::class);
        $status = new SystemChangeOrderShipmentStatus($orderShipmentStatusService);

        self::expectException(InvalidOrderShipmentStatusException::class);

        $status->change(\Mockery::mock(Order::class), 'INVALID_ORDER_STATUS');
    }

    public function testItSkipChangingOrderShipmentStatus()
    {
        foreach ([OrderShipmentStatus::PREPARING, OrderShipmentStatus::CANCELED] as $nextStatus) {
            $order = \Mockery::mock(Order::class);
            $order->shouldReceive('getShipments')->once()->withNoArgs()->andReturnUsing(function () use ($nextStatus) {
                $shipment = \Mockery::mock(OrderShipment::class);
                $shipment->shouldReceive('getStatus')->once()->withNoArgs()->andReturn($nextStatus);

                return new ArrayCollection([$shipment]);
            });

            $orderShipmentStatusService = \Mockery::mock(OrderShipmentStatusService::class);
            $orderShipmentStatusService->shouldNotReceive('change');

            $status = new SystemChangeOrderShipmentStatus($orderShipmentStatusService);

            $status->change($order, $nextStatus);
        }
    }

    public function testItChangeOrderShipmentsStatuses()
    {
        $order = \Mockery::mock(Order::class);
        $order->shouldReceive('getShipments')->once()->withNoArgs()->andReturnUsing(function () {
            $shipment = \Mockery::mock(OrderShipment::class);
            $shipment->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(OrderShipmentStatus::NEW);

            return new ArrayCollection([$shipment]);
        });

        $orderShipmentStatusService = \Mockery::mock(OrderShipmentStatusService::class);
        $orderShipmentStatusService->shouldReceive('change')
                                   ->once()
                                   ->with(
                                       \Mockery::type(OrderShipment::class),
                                       OrderShipmentStatus::WAITING_FOR_SUPPLY
                                   )
                                   ->andReturn();

        $status = new SystemChangeOrderShipmentStatus($orderShipmentStatusService);

        $status->change($order, OrderShipmentStatus::WAITING_FOR_SUPPLY);
    }
}
