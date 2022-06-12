<?php

namespace App\Tests\Unit\Service\Order\OrderStatus;

use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Service\Order\OrderStatus\CallFailedOrderStatus;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusTransitionException;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\OrderShipment\SystemChangeOrderShipmentStatus\SystemChangeOrderShipmentStatus;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CallFailedOrderStatusTest extends MockeryTestCase
{
    /**
     * @var Order|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $orderMock;

    /**
     * @var SystemChangeOrderShipmentStatus|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderShipmentStatusService;

    protected CallFailedOrderStatus $callFailedOrderStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderMock = \Mockery::mock(Order::class);

        $this->orderShipmentStatusService = \Mockery::mock(OrderShipmentStatusService::class);

        $this->callFailedOrderStatus = new CallFailedOrderStatus($this->orderShipmentStatusService);
    }

    protected function tearDown(): void
    {
        unset($this->callFailedOrderStatus);

        $this->orderMock = null;
        $this->orderShipmentStatusService = null;
    }

    public function testItCanSetOrderToWaitCustomerWithMock()
    {
        $this->orderMock->shouldReceive('setStatus')->once()->with(OrderStatus::WAIT_CUSTOMER)->andReturnSelf();

        $this->callFailedOrderStatus->waitCustomer($this->orderMock);
    }

    /**
     * @dataProvider supportProvider
     */
    public function testSupport($status, $assert)
    {
        $result = $this->callFailedOrderStatus->support($status);

        self::assertEquals($assert, $result);
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testItThrowsExceptionWhenOrderStatusTransitionIsInvalid($method)
    {
        $this->expectException(InvalidOrderStatusTransitionException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order status transition is invalid!');

        $this->callFailedOrderStatus->{$method}($this->orderMock);
    }

    public function supportProvider()
    {
        $orderStatuses = OrderStatus::toArray();

        return array_map(function ($status) {
            return array($status, ($status === OrderStatus::CALL_FAILED));
        }, $orderStatuses);
    }

    public function testItCanGetValidTransitions()
    {
        $result = $this->callFailedOrderStatus->validTransitions();

        self::assertEquals([
            OrderStatus::WAIT_CUSTOMER,
            OrderStatus::CANCELED,
            OrderStatus::REFUND,
        ], $result);
    }

    public function exceptionProvider()
    {
        return array_map(function ($method) {
            return array($method);
        }, [
            'new',
            'callFailed',
            'waitingForPay',
            'confirmed',
            'delivered',
            'canceledSystem',
        ]);
    }
}
