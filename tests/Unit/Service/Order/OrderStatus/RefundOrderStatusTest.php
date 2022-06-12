<?php

namespace App\Tests\Unit\Service\Order\OrderStatus;

use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusTransitionException;
use App\Service\Order\OrderStatus\RefundOrderStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\OrderShipment\SystemChangeOrderShipmentStatus\SystemChangeOrderShipmentStatus;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class RefundOrderStatusTest extends MockeryTestCase
{
    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderMock;

    /**
     * @var SystemChangeOrderShipmentStatus|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderShipmentStatusService;

    protected RefundOrderStatus $refundOrderStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderMock = Mockery::mock(Order::class);

        $this->orderShipmentStatusService = Mockery::mock(OrderShipmentStatusService::class);

        $this->refundOrderStatus = new RefundOrderStatus($this->orderShipmentStatusService);
    }

    protected function tearDown(): void
    {
        unset($this->orderMock, $this->orderShipmentStatusService, $this->refundOrderStatus);
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testItThrowsExceptionWhenOrderStatusTransitionIsInvalid($method)
    {
        $this->expectException(InvalidOrderStatusTransitionException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order status transition is invalid!');

        $this->refundOrderStatus->{$method}($this->orderMock);
    }

    /**
     * @dataProvider supportProvider
     */
    public function testSupport($status, $assert)
    {
        $result = $this->refundOrderStatus->support($status);

        self::assertEquals($assert, $result);
    }

    public function supportProvider()
    {
        $orderStatuses = OrderStatus::toArray();

        return array_map(function ($status) {
            return array($status, ($status === OrderStatus::REFUND));
        }, $orderStatuses);
    }

    public function testItCanGetValidTransitions()
    {
        $result = $this->refundOrderStatus->validTransitions();

        self::assertEquals([
            OrderStatus::CANCELED,
        ], $result);
    }

    public function exceptionProvider()
    {
        return array_map(function ($method) {
            return array($method);
        }, [
            'new',
            'waitCustomer',
            'callFailed',
            'waitingForPay',
            'confirmed',
            'delivered',
            'canceledSystem',
            'refund',
        ]);
    }
}
