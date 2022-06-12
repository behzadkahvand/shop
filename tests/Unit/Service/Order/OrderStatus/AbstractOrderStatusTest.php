<?php

namespace App\Tests\Unit\Service\Order\OrderStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Entity\OrderShipment;
use App\Service\Order\OrderStatus\AbstractOrderStatus;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusTransitionException;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AbstractOrderStatusTest extends MockeryTestCase
{
    protected $orderMock;

    protected $orderShipmentStatusService;

    protected $abstractOrderStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderMock = Mockery::mock(Order::class);

        $this->orderShipmentStatusService = Mockery::mock(OrderShipmentStatusService::class);

        $this->abstractOrderStatus = new class ($this->orderShipmentStatusService) extends AbstractOrderStatus {
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

    protected function tearDown(): void
    {
        unset($this->orderMock, $this->orderShipmentStatusService, $this->abstractOrderStatus);
    }

    /**
     * @dataProvider provider
     */
    public function testAbstractOrderStatus($method)
    {
        $this->expectException(InvalidOrderStatusTransitionException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order status transition is invalid!');

        $this->abstractOrderStatus->{$method}($this->orderMock);
    }

    public function testItCanSetOrderToCanceledWithMock()
    {
        $this->orderMock->shouldReceive('setStatus')
                        ->once()
                        ->with(OrderStatus::CANCELED)
                        ->andReturnSelf();

        $orderShipment = Mockery::mock(OrderShipment::class);
        $orderShipment->shouldReceive('getStatus')
                      ->once()
                      ->withNoArgs()
                      ->andReturn(OrderShipmentStatus::WAITING_FOR_SUPPLY);

        $this->orderMock->shouldReceive('getShipments')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$orderShipment]));

        $this->orderShipmentStatusService->shouldReceive('change')
                                         ->once()
                                         ->with($orderShipment, OrderShipmentStatus::CANCELED)
                                         ->andReturn();

        $this->abstractOrderStatus->canceled($this->orderMock);
    }

    public function testItCanSetOrderToCanceledWithObject()
    {
        $orderShipment = Mockery::mock(OrderShipment::class);
        $orderShipment->shouldReceive('getStatus')
                      ->once()
                      ->withNoArgs()
                      ->andReturn(OrderShipmentStatus::WAITING_FOR_SUPPLY);

        $this->orderMock->shouldReceive('getShipments')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$orderShipment]));

        $this->orderMock->shouldReceive('setStatus')
                        ->once()
                        ->with(OrderShipmentStatus::CANCELED)
                        ->andReturnSelf();

        $this->orderShipmentStatusService->shouldReceive('change')
                                         ->once()
                                         ->with($orderShipment, OrderShipmentStatus::CANCELED)
                                         ->andReturn();

        $this->abstractOrderStatus->canceled($this->orderMock);
    }

    public function testItCanSetOrderToRefundWithMock()
    {
        $this->orderMock->shouldReceive('setStatus')->once()->with(OrderStatus::REFUND)->andReturnSelf();

        $this->abstractOrderStatus->refund($this->orderMock);
    }

    public function provider()
    {
        $methods = get_class_methods(AbstractOrderStatus::class);

        unset($methods[array_search('__construct', $methods)]);
        unset($methods[array_search('support', $methods)]);
        unset($methods[array_search('validTransitions', $methods)]);
        unset($methods[array_search('canceled', $methods)]);
        unset($methods[array_search('refund', $methods)]);
        unset($methods[array_search('changeShipmentStatuses', $methods)]);

        return array_map(function ($method) {
            return array($method);
        }, $methods);
    }
}
