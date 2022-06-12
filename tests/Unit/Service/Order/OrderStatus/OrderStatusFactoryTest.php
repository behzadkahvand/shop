<?php

namespace App\Tests\Unit\Service\Order\OrderStatus;

use App\Dictionary\OrderStatus;
use App\Service\Order\OrderStatus\CallFailedOrderStatus;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusTransitionException;
use App\Service\Order\OrderStatus\OrderStatusFactory;
use App\Service\Order\OrderStatus\WaitCustomerOrderStatus;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OrderStatusFactoryTest extends MockeryTestCase
{
    private OrderStatusFactory $orderStatusFactory;

    private WaitCustomerOrderStatus|Mockery\LegacyMockInterface|Mockery\MockInterface|null $waitCustomerOrderStatusMock;

    private Mockery\LegacyMockInterface|Mockery\MockInterface|CallFailedOrderStatus|null $callFailedOrderStatusMock;


    protected function setUp(): void
    {
        parent::setUp();

        $this->waitCustomerOrderStatusMock = Mockery::mock(WaitCustomerOrderStatus::class);

        $this->callFailedOrderStatusMock = Mockery::mock(CallFailedOrderStatus::class);

        $this->orderStatusFactory = new OrderStatusFactory([
            $this->waitCustomerOrderStatusMock,
            $this->callFailedOrderStatusMock
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->orderStatusFactory);

        $this->waitCustomerOrderStatusMock = null;
        $this->callFailedOrderStatusMock = null;
    }

    public function testItThrowsExceptionWhenItDoesNotSupportTheTransition()
    {
        $this->waitCustomerOrderStatusMock->shouldReceive('support')->once()->with(OrderStatus::CANCELED_SYSTEM)->andReturn(false);
        $this->callFailedOrderStatusMock->shouldReceive('support')->once()->with(OrderStatus::CANCELED_SYSTEM)->andReturn(false);

        $this->expectException(InvalidOrderStatusTransitionException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order status transition is invalid!');

        $this->orderStatusFactory->create(OrderStatus::CANCELED_SYSTEM);
    }

    public function testItCanCreateOrderStatus()
    {
        $this->waitCustomerOrderStatusMock->shouldReceive('support')->once()->with(OrderStatus::WAIT_CUSTOMER)->andReturn(true);

        $result = $this->orderStatusFactory->create(OrderStatus::WAIT_CUSTOMER);

        self::assertEquals($result, $this->waitCustomerOrderStatusMock);
    }

    public function testItCanGetCreateOrderStatusLogValueObject()
    {
        $result = $this->orderStatusFactory->getCreateOrderStatusLogValueObject();

        self::assertInstanceOf(CreateOrderStatusLogValueObject::class, $result);
    }
}
